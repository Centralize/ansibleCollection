<?php
// #############################################################################
// # CONFIGURATION & SETUP
// #############################################################################
$db_file = 'tasks.db';
date_default_timezone_set('Europe/Copenhagen');

$danish_days = ['Mandag', 'Tirsdag', 'Onsdag', 'Torsdag', 'Fredag', 'Lørdag', 'Søndag'];

// #############################################################################
// # DATABASE INITIALIZATION
// #############################################################################
try {
    $pdo = new PDO('sqlite:' . $db_file);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // ** SKEMA OPDATERET for at understøtte forskellige gentagelsestyper **
    // is_recurring: 0 = No repeat, 1 = Daily, 2 = Weekly
    $pdo->exec("CREATE TABLE IF NOT EXISTS tasks (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        title TEXT NOT NULL,
        status TEXT NOT NULL DEFAULT 'pending',
        category TEXT NOT NULL DEFAULT 'chore',
        task_datetime DATETIME NULL,
        is_recurring INTEGER NOT NULL DEFAULT 0,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        completed_at DATETIME NULL
    )");

} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

// #############################################################################
// # DATE & WEEK CALCULATION
// #############################################################################
$target_date = new DateTime();
$week_param = $_GET['week'] ?? '';

if (preg_match('/^(\d{4})-W(\d{2})$/', $week_param, $matches)) {
    $target_date->setISODate((int)$matches[1], (int)$matches[2]);
}

$week_start = (clone $target_date)->modify('monday this week');
$week_end = (clone $week_start)->modify('+6 days');
$current_week_string = $week_start->format('Y-\WW');

$prev_week = (clone $week_start)->modify('-1 week')->format('Y-\WW');
$next_week = (clone $week_start)->modify('+1 week')->format('Y-\WW');

// #############################################################################
// # LOGIC: HANDLE POST & GET REQUESTS
// #############################################################################

// --- Tilføj ny opgave ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_task'])) {
    $title = trim($_POST['title']);
    $category = trim($_POST['category']);
    $task_datetime = !empty($_POST['task_datetime']) ? $_POST['task_datetime'] : NULL;
    // Hent gentagelsestype fra dropdown (0, 1, eller 2)
    $is_recurring = (int)($_POST['recurring_type'] ?? 0);
    $week_to_stay_on = $_POST['week'] ?? '';

    if (!empty($title) && !empty($task_datetime)) {
        $stmt = $pdo->prepare("INSERT INTO tasks (title, category, task_datetime, is_recurring) VALUES (?, ?, ?, ?)");
        $stmt->execute([$title, $category, $task_datetime, $is_recurring]);
    }
    header('Location: index.php?week=' . urlencode($week_to_stay_on));
    exit;
}

// --- Håndter sletning ---
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action'])) {
    $task_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    if ($task_id > 0 && $_GET['action'] === 'delete') {
        $stmt = $pdo->prepare("DELETE FROM tasks WHERE id = ?");
        $stmt->execute([$task_id]);
        header('Location: index.php?week=' . urlencode($week_param));
        exit;
    }
}

// #############################################################################
// # DATA FETCHING & STRUCTURING
// #############################################################################
$calendar_tasks = [];

// 1. Hent normale, ikke-gentagne opgaver for den valgte uge
$stmt = $pdo->prepare("SELECT * FROM tasks WHERE is_recurring = 0 AND task_datetime BETWEEN ? AND ?");
$stmt->execute([$week_start->format('Y-m-d 00:00:00'), $week_end->format('Y-m-d 23:59:59')]);
$normal_tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($normal_tasks as $task) {
    $task_date = new DateTime($task['task_datetime']);
    $day_index = (int)$task_date->format('N') - 1;
    $hour_index = (int)$task_date->format('G');
    if (!isset($calendar_tasks[$day_index][$hour_index])) $calendar_tasks[$day_index][$hour_index] = [];
    $calendar_tasks[$day_index][$hour_index][] = $task;
}

// 2. Hent ALLE gentagne opgaver (både daglige og ugentlige)
$stmt = $pdo->query("SELECT * FROM tasks WHERE is_recurring > 0");
$recurring_tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 3. Placer de gentagne opgaver i kalenderen
foreach ($recurring_tasks as $task) {
    $task_time = new DateTime($task['task_datetime']);
    $hour_index = (int)$task_time->format('G');

    if ($task['is_recurring'] == 1) { // Daglig gentagelse
        for ($day_index = 0; $day_index < 7; $day_index++) {
            if (!isset($calendar_tasks[$day_index][$hour_index])) $calendar_tasks[$day_index][$hour_index] = [];
            $calendar_tasks[$day_index][$hour_index][] = $task;
        }
    } elseif ($task['is_recurring'] == 2) { // Ugentlig gentagelse
        $task_day_of_week = (int)$task_time->format('N') - 1; // 0=Mandag, 1=Tirsdag...
        $day_index = $task_day_of_week;
        if (!isset($calendar_tasks[$day_index][$hour_index])) $calendar_tasks[$day_index][$hour_index] = [];
        $calendar_tasks[$day_index][$hour_index][] = $task;
    }
}

?>
<!DOCTYPE html>
<html lang="da">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ugeplanlægger</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; background-color: #f4f4f9; color: #333; margin: 0; padding: 20px; }
        .container { max-width: 1600px; margin: auto; background: #fff; padding: 25px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .header-area { display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #ecf0f1; padding-bottom: 10px; }
        h1 { margin: 0; }
        h2 { margin: 0; color: #7f8c8d; font-size: 1.2em; font-weight: normal; }
        .week-nav { display: flex; gap: 10px; }
        .week-nav a { display: inline-block; padding: 8px 15px; background-color: #3498db; color: white; text-decoration: none; border-radius: 4px; transition: background-color 0.2s; }
        .week-nav a:hover { background-color: #2980b9; }
        .week-nav a.today { background-color: #95a5a6; }
        .task-form { background: #ecf0f1; padding: 20px; border-radius: 5px; margin-top: 20px; margin-bottom: 30px; display: flex; align-items: flex-end; gap: 15px; flex-wrap: wrap; }
        .form-group { display: flex; flex-direction: column; }
        .form-group label { margin-bottom: 5px; font-size: 0.9em; color: #555; }
        .task-form input[type="text"], .task-form input[type="datetime-local"], .task-form select { padding: 10px; border: 1px solid #bdc3c7; border-radius: 4px; font-size: 1em; height: 42px; box-sizing: border-box; }
        .task-form button { background-color: #3498db; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; font-size: 16px; height: 42px; }
        .task-form button:hover { background-color: #2980b9; }

        .calendar-table { width: 100%; border-collapse: collapse; table-layout: fixed; margin-top: 20px; }
        .calendar-table th, .calendar-table td { border: 1px solid #ddd; padding: 8px; vertical-align: top; }
        .calendar-table th { background-color: #34495e; color: white; text-align: center; }
        .time-slot { width: 100px; text-align: center; font-weight: bold; background-color: #f8f9fa; }
        .calendar-table td { height: 80px; }
        .task { background-color: #3498db; color: white; padding: 5px; margin-bottom: 5px; border-radius: 4px; font-size: 0.85em; overflow: hidden; position: relative; }
        .task.category-work { background-color: #e74c3c; }
        .task.category-personal { background-color: #2ecc71; }
        .task.category-shopping { background-color: #f39c12; }
        /* Forskellige farver for gentagelsestyper */
        .task.is-recurring-daily { border-left: 4px solid #9b59b6; } /* Lilla for daglig */
        .task.is-recurring-weekly { border-left: 4px solid #e67e22; } /* Orange for ugentlig */
        .task-title { font-weight: bold; }
        .task-time { font-size: 0.9em; }
        .task-delete { position: absolute; top: 2px; right: 4px; color: white; text-decoration: none; font-weight: bold; display: none; }
        .task:hover .task-delete { display: block; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header-area">
            <div>
                <h1>Ugeplanlægger</h1>
                <h2>
                    Uge <?php echo $week_start->format('W'); ?>: 
                    <?php echo $week_start->format('d/m') . ' - ' . $week_end->format('d/m/Y'); ?>
                </h2>
            </div>
            <div class="week-nav">
                <a href="?week=<?php echo $prev_week; ?>">« Forrige Uge</a>
                <a href="index.php" class="today">Denne Uge</a>
                <a href="?week=<?php echo $next_week; ?>">Næste Uge »</a>
            </div>
        </div>

        <div class="task-form">
            <form action="index.php?week=<?php echo htmlspecialchars($current_week_string); ?>" method="post" style="display: contents;">
                <input type="hidden" name="week" value="<?php echo htmlspecialchars($current_week_string); ?>">

                <div class="form-group">
                    <label for="title">Opgave</label>
                    <input type="text" id="title" name="title" placeholder="Titel på opgave" required>
                </div>
                <div class="form-group">
                    <label for="task_datetime">Dato og Tidspunkt</label>
                    <input type="datetime-local" id="task_datetime" name="task_datetime" required>
                </div>
                 <div class="form-group">
                    <label for="category">Kategori</label>
                    <select name="category" id="category">
                        <option value="chore">Pligt</option>
                        <option value="work">Arbejde</option>
                        <option value="personal">Personlig</option>
                        <option value="shopping">Indkøb</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="recurring_type">Gentagelse</label>
                    <select name="recurring_type" id="recurring_type">
                        <option value="0">Aldrig</option>
                        <option value="1">Dagligt</option>
                        <option value="2">Ugentligt</option>
                    </select>
                </div>
                <button type="submit" name="add_task">Tilføj Opgave</button>
            </form>
        </div>

        <table class="calendar-table">
            <thead>
                <tr>
                    <th>Tid</th>
                    <?php foreach ($danish_days as $day): ?>
                        <th><?php echo $day; ?></th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <?php for ($hour = 8; $hour <= 20; $hour++): ?>
                    <tr>
                        <td class="time-slot"><?php printf('%02d:00', $hour); ?></td>
                        <?php for ($day_index = 0; $day_index < 7; $day_index++): ?>
                            <td>
                                <?php if (isset($calendar_tasks[$day_index][$hour])): ?>
                                    <?php foreach ($calendar_tasks[$day_index][$hour] as $task): ?>
                                        <?php 
                                            $task_class = 'task category-' . htmlspecialchars($task['category']);
                                            if ($task['is_recurring'] == 1) {
                                                $task_class .= ' is-recurring-daily';
                                            } elseif ($task['is_recurring'] == 2) {
                                                $task_class .= ' is-recurring-weekly';
                                            }
                                        ?>
                                        <div class="<?php echo $task_class; ?>">
                                            <a href="?action=delete&id=<?php echo $task['id']; ?>&week=<?php echo htmlspecialchars($current_week_string); ?>" class="task-delete" title="Slet opgave" onclick="return confirm('Er du sikker på du vil slette denne opgave? Bemærk: Hvis opgaven er gentagende, slettes den permanent.');">×</a>
                                            <div class="task-title"><?php echo htmlspecialchars($task['title']); ?></div>
                                            <div class="task-time"><?php echo (new DateTime($task['task_datetime']))->format('H:i'); ?></div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </td>
                        <?php endfor; ?>
                    </tr>
                <?php endfor; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
