<?php
// ##################################################################
// #                        PHP-LOGIK & DATABASE                      #
// ##################################################################

// Sæt fejlrapportering til under udvikling. Fjern i produktion.
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Definer stien til SQLite-databasen. Den vil blive oprettet i samme mappe som dette script.
define('DATABASE_PATH', 'sqlite:' . __DIR__ . '/opskrifter.sqlite');

/**
 * Opretter en PDO-databaseforbindelse og sikrer, at tabellen 'opskrifter' eksisterer.
 * @return PDO
 */
function get_db_connection(): PDO {
    try {
        $pdo = new PDO(DATABASE_PATH);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        die("Databasefejl: Kunne ikke oprette forbindelse. " . $e->getMessage());
    }

    // Opret tabel hvis den ikke findes
    $pdo->exec("CREATE TABLE IF NOT EXISTS opskrifter (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        titel TEXT NOT NULL,
        beskrivelse TEXT,
        ingredienser TEXT,
        instruktioner TEXT,
        forberedelsestid INTEGER,
        tilberedningstid INTEGER,
        antal_personer INTEGER,
        oprettet_dato TEXT DEFAULT CURRENT_TIMESTAMP
    )");

    return $pdo;
}

// Initialiser databaseforbindelsen
$pdo = get_db_connection();

// Håndter formular-indsendelser (POST requests) for at tilføje eller redigere
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    switch ($_POST['action']) {
        case 'add':
            $sql = "INSERT INTO opskrifter (titel, beskrivelse, ingredienser, instruktioner, forberedelsestid, tilberedningstid, antal_personer) VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $_POST['titel'],
                $_POST['beskrivelse'],
                $_POST['ingredienser'],
                $_POST['instruktioner'],
                $_POST['forberedelsestid'],
                $_POST['tilberedningstid'],
                $_POST['antal_personer']
            ]);
            break;
        case 'edit':
            $sql = "UPDATE opskrifter SET titel = ?, beskrivelse = ?, ingredienser = ?, instruktioner = ?, forberedelsestid = ?, tilberedningstid = ?, antal_personer = ? WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $_POST['titel'],
                $_POST['beskrivelse'],
                $_POST['ingredienser'],
                $_POST['instruktioner'],
                $_POST['forberedelsestid'],
                $_POST['tilberedningstid'],
                $_POST['antal_personer'],
                $_POST['id']
            ]);
            break;
    }
    // Gå tilbage til forsiden efter handlingen for at undgå genindsendelse af formularen
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// Håndter sletning (GET request)
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $stmt = $pdo->prepare('DELETE FROM opskrifter WHERE id = ?');
    $stmt->execute([$_GET['id']]);
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// ##################################################################
// #                       HTML & CSS TEMPLATES                       #
// ##################################################################

/**
 * Viser toppen af HTML-siden, inklusiv CSS.
 * @param string $page_title
 */
function render_header(string $page_title = 'Mit Opskriftskatalog'): void {
    echo <<<HTML
<!DOCTYPE html>
<html lang="da">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{$page_title}</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; line-height: 1.6; margin: 0; padding: 0; background: #fdfdfd; color: #333; }
        header { background: #2c3e50; color: #ecf0f1; padding: 1rem 0; text-align: center; }
        header h1 a { color: #ecf0f1; text-decoration: none; }
        nav a { color: #fff; margin: 0 15px; text-decoration: none; background-color: #3498db; padding: 8px 15px; border-radius: 5px; }
        main { padding: 20px; max-width: 800px; margin: 20px auto; background: #fff; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        footer { text-align: center; padding: 20px; margin-top: 40px; background: #ecf0f1; color: #7f8c8d; }
        .opskrift-liste .opskrift-item { border-bottom: 1px solid #ecf0f1; padding: 15px 0; }
        .opskrift-liste .opskrift-item:last-child { border-bottom: none; }
        .opskrift-liste h3 a { text-decoration: none; color: #2980b9; }
        .opskrift-visning h2 { border-bottom: 2px solid #3498db; padding-bottom: 10px; }
        .opskrift-meta { margin: 20px 0; padding: 10px; background-color: #ecf0f1; border-radius: 5px; display: flex; justify-content: space-around; }
        .ingredienser, .instruktioner { background: #f9f9f9; padding: 15px; border: 1px solid #eee; border-radius: 5px; margin-bottom: 20px; white-space: pre-wrap; }
        form label { display: block; margin-top: 15px; font-weight: bold; }
        form input[type="text"], form input[type="number"], form textarea { width: 100%; padding: 10px; margin-top: 5px; border-radius: 4px; border: 1px solid #ddd; box-sizing: border-box; }
        form button { display: inline-block; background: #27ae60; color: #fff; padding: 12px 20px; border: none; cursor: pointer; margin-top: 20px; border-radius: 5px; font-size: 16px; }
        .opskrift-handlinger { margin-top: 20px; }
        .opskrift-handlinger a { display: inline-block; padding: 8px 12px; border-radius: 5px; text-decoration: none; color: #fff; margin-right: 10px; }
        .rediger-knap { background: #f39c12; }
        .slet-knap { background: #e74c3c; }
    </style>
</head>
<body>
    <header>
        <h1><a href="{$_SERVER['PHP_SELF']}">Mit Opskriftskatalog</a></h1>
        <nav>
            <a href="{$_SERVER['PHP_SELF']}?action=add">Tilføj ny opskrift</a>
        </nav>
    </header>
    <main>
HTML;
}

/**
 * Viser bunden af HTML-siden.
 */
function render_footer(): void {
    $year = date("Y");
    echo <<<HTML
    </main>
    <footer>
        <p>&copy; {$year} Mit Opskriftskatalog</p>
    </footer>
</body>
</html>
HTML;
}

// ##################################################################
// #                       SIDENS INDHOLD (VIEWS)                     #
// ##################################################################

/**
 * Viser en liste over alle opskrifter.
 * @param PDO $pdo
 */
function display_recipe_list(PDO $pdo): void {
    echo '<h2>Alle Opskrifter</h2>';
    $stmt = $pdo->query('SELECT * FROM opskrifter ORDER BY titel ASC');
    $opskrifter = $stmt->fetchAll();

    if (empty($opskrifter)) {
        echo "<p>Ingen opskrifter fundet endnu. <a href='?action=add'>Tilføj den første!</a></p>";
    } else {
        echo '<div class="opskrift-liste">';
        foreach ($opskrifter as $opskrift) {
            echo '<div class="opskrift-item">';
            echo '<h3><a href="?action=view&id=' . $opskrift['id'] . '">' . htmlspecialchars($opskrift['titel']) . '</a></h3>';
            echo '<p>' . htmlspecialchars($opskrift['beskrivelse']) . '</p>';
            echo '</div>';
        }
        echo '</div>';
    }
}

/**
 * Viser en enkelt opskrift i detaljer.
 * @param PDO $pdo
 * @param int $id
 */
function display_single_recipe(PDO $pdo, int $id): void {
    $stmt = $pdo->prepare('SELECT * FROM opskrifter WHERE id = ?');
    $stmt->execute([$id]);
    $opskrift = $stmt->fetch();

    if (!$opskrift) {
        echo '<h2>Opskrift ikke fundet</h2>';
        return;
    }

    echo '<div class="opskrift-visning">';
    echo '<h2>' . htmlspecialchars($opskrift['titel']) . '</h2>';
    echo '<div class="opskrift-meta">';
    echo '<span><strong>Forberedelse:</strong> ' . ($opskrift['forberedelsestid'] ?? '?') . ' min</span>';
    echo '<span><strong>Tilberedning:</strong> ' . ($opskrift['tilberedningstid'] ?? '?') . ' min</span>';
    echo '<span><strong>Antal personer:</strong> ' . ($opskrift['antal_personer'] ?? '?') . '</span>';
    echo '</div>';
    echo '<p><em>' . htmlspecialchars($opskrift['beskrivelse']) . '</em></p>';

    echo '<h3>Ingredienser</h3>';
    echo '<div class="ingredienser">' . htmlspecialchars($opskrift['ingredienser']) . '</div>';

    echo '<h3>Instruktioner</h3>';
    echo '<div class="instruktioner">' . htmlspecialchars($opskrift['instruktioner']) . '</div>';

    echo '<div class="opskrift-handlinger">';
    echo '<a href="?action=edit&id=' . $opskrift['id'] . '" class="rediger-knap">Redigér</a>';
    echo '<a href="?action=delete&id=' . $opskrift['id'] . '" onclick="return confirm(\'Er du sikker på, du vil slette denne opskrift?\');" class="slet-knap">Slet</a>';
    echo '</div>';
    echo '</div>';
}

/**
 * Viser formularen til at tilføje/redigere en opskrift.
 * @param array|null $opskrift
 */
function display_form(array $opskrift = null): void {
    $is_edit = $opskrift !== null;
    $action = $is_edit ? 'edit' : 'add';
    $title = $is_edit ? 'Redigér Opskrift' : 'Tilføj Ny Opskrift';

    echo "<h2>{$title}</h2>";
    echo "<form action='{$_SERVER['PHP_SELF']}' method='post'>";
    echo "<input type='hidden' name='action' value='{$action}'>";
    if ($is_edit) {
        echo "<input type='hidden' name='id' value='" . $opskrift['id'] . "'>";
    }

    $form_fields = [
        'titel' => ['label' => 'Titel:', 'type' => 'text', 'required' => true],
        'beskrivelse' => ['label' => 'Beskrivelse:', 'type' => 'textarea', 'rows' => 3],
        'ingredienser' => ['label' => 'Ingredienser (én pr. linje):', 'type' => 'textarea', 'rows' => 10],
        'instruktioner' => ['label' => 'Instruktioner:', 'type' => 'textarea', 'rows' => 15],
        'forberedelsestid' => ['label' => 'Forberedelsestid (minutter):', 'type' => 'number'],
        'tilberedningstid' => ['label' => 'Tilberedningstid (minutter):', 'type' => 'number'],
        'antal_personer' => ['label' => 'Antal personer:', 'type' => 'number']
    ];
    
    foreach ($form_fields as $name => $field) {
        $value = $is_edit ? htmlspecialchars($opskrift[$name]) : '';
        echo "<label for='{$name}'>{$field['label']}</label>";
        if ($field['type'] === 'textarea') {
            echo "<textarea id='{$name}' name='{$name}' rows='{$field['rows']}'>{$value}</textarea>";
        } else {
            $required = isset($field['required']) ? 'required' : '';
            echo "<input type='{$field['type']}' id='{$name}' name='{$name}' value='{$value}' {$required}>";
        }
    }

    $button_text = $is_edit ? 'Opdatér Opskrift' : 'Gem Opskrift';
    echo "<button type='submit'>{$button_text}</button>";
    echo "</form>";
}

// ##################################################################
// #                 ROUTER - BESTEM HVILKEN SIDE DER SKAL VISES      #
// ##################################################################

// Hent 'action' fra URL'en, f.eks. ?action=view
$action = $_GET['action'] ?? 'list';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

render_header();

switch ($action) {
    case 'view':
        if ($id > 0) {
            display_single_recipe($pdo, $id);
        } else {
            display_recipe_list($pdo);
        }
        break;
    case 'add':
        display_form();
        break;
    case 'edit':
        if ($id > 0) {
            $stmt = $pdo->prepare('SELECT * FROM opskrifter WHERE id = ?');
            $stmt->execute([$id]);
            $opskrift = $stmt->fetch();
            if ($opskrift) {
                display_form($opskrift);
            }
        }
        break;
    default:
        display_recipe_list($pdo);
        break;
}

render_footer();

?>
