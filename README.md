# Ansible Collection

This repository, "Ansible Collection," serves as a centralized collection of Ansible Playbooks designed to automate various system administration tasks and application deployments. Each playbook is organized into its own dedicated directory, making it easy to manage, use, and extend.

## Project Structure

The project is structured to provide clear organization and ease of use:

-   **Root Directory**: Contains general project files such as `README.md`, `CHANGELOG`, `LICENSE`, and utility scripts.
-   **Module Directories**: Each top-level directory (e.g., `awx/`, `lamp/`, `nextcloud/`) represents a distinct Ansible module or application deployment.
    -   **`inventory`**: Defines the target hosts for the specific playbook.
    -   **`main.yml`**: The primary Ansible playbook for the module, containing the tasks to be executed.
    -   **Supporting Files**: May include `docker-compose.yml.j2`, `requirements.yml`, `templates/`, `files/`, `group_vars/`, `roles/`, etc., depending on the module's requirements.

## Available Playbooks

Below is a list of the Ansible Playbooks available in this collection, along with a brief description of their purpose:

-   **AWX**
    Deployment of Ansible AWX, a web-based UI for Ansible.
-   **Bind/Named DNS**
    Deployment of Bind 9 DNS server.
-   **Cloudflare DNS**
    Manages DNS records via the Cloudflare API.
-   **Create new VPS**
    Automates the creation of new Virtual Private Servers on OPNLAN Cloud.
-   **default-users**
    Creates and sets up default user accounts with authorized keys.
-   **Docker**
    Installs and configures Docker.
-   **ERPNext**
    Deployment of ERPNext, an open-source ERP solution.
-   **GitLab**
    Deployment of GitLab Enterprise Edition.
-   **Home Assistant**
    Installation and configuration of Home Assistant for home automation.
-   **Hushlogin**
    Creates a `.hushlogin` file to suppress login messages.
-   **Incus**
    Deployment of Incus, a system container and virtual machine manager.
-   **instSalt**
    Installs and configures SaltStack master and minions.
-   **IRC-Server**
    Deployment of an IRC server.
-   **iVentoy**
    Deployment of iVentoy PXE Boot Server for OS deployment.
-   **Jitsi**
    Deployment of Jitsi Meet for video conferencing.
-   **k8s**
    Deployment of a standalone Kubernetes cluster.
-   **k9s**
    Deployment of K9s Console for Kubernetes cluster management.
-   **Kafka**
    Deployment of Apache Kafka server.
-   **LAMP**
    Installs and configures Apache 2.x, PHP 8.1, MySQL 8.x, and PHPmyAdmin.
-   **LTSP**
    Deployment of Linux Terminal Server Project.
-   **LXD**
    Deployment of LXD, a system container manager.
-   **LXD-Dashboard**
    Deployment of a web dashboard for LXD.
-   **MkDocs**
    Installs and configures MkDocs for project documentation.
-   **MySQL**
    Installs and configures MySQL database server.
-   **NagiosXI**
    Deployment of Nagios XI monitoring solution.
-   **NCPA**
    Installs and configures NCPA (Nagios Cross Platform Agent).
-   **Netbird**
    Installs and configures Netbird VPN client.
-   **Network Service Platform**
    Manages network services and configurations.
-   **Nextcloud**
    Deployment of Nextcloud Server for file management, calendar, and documentation.
-   **Nginx**
    Installs and configures Nginx web server.
-   **NginxGen**
    Generates Nginx configuration files.
-   **NPMPlus**
    Deployment of Nginx Proxy Manager Plus.
-   **OpenTofu**
    Installs and configures HashiCorp Terraform fork.
-   **OpenXPKI**
    Deployment of OpenXPKI, a certificate authority.
-   **postinstall-workstation**
    Post-installation script for workstation setup.
-   **PXE**
    Sets up a PXE Boot Server.
-   **rmSnapd**
    Removes Snapd from the system.
-   **Seedbox (rtorrent/rutorrent)**
    Sets up one or more seedboxes with rtorrent/rutorrent.
-   **Sensu Go**
    Deployment of Sensu Go Monitoring Server.
-   **Sensu Go Agent**
    Deployment of Sensu Go Agent for monitoring.
-   **Technitium**
    Installs and configures Technitium DNS Server.
-   **Terminal Server**
    Sets up a terminal server.
-   **Update**
    Updates package cache and all installed packages.
-   **Update-SuSE**
    Updates package cache and packages specifically for SuSE distributions.
-   **Vaults**
    Deployment of HashiCorp Vault for secret management.
-   **Weekplanner**
    Installs and configures Weekplanner application.
-   **WordPress**
    Deployment of WordPress content management system.
-   **Xymon**
    Deployment of Xymon Monitoring Server.
-   **Zabbix**
    Deployment of Zabbix monitoring server.
-   **Zsh**
    Installs and configures Zsh shell.

## Usage

### Ansible Installation

To ensure Ansible is installed on your system, use the provided `install.ansible` script:

```bash
./install.ansible
```

This script will update your package lists, add the Ansible PPA, and install Ansible.

### Running Playbooks

To execute a specific playbook, navigate to the root of this repository and use the `ansible-playbook` command, specifying the `main.yml` file and its corresponding `inventory` file within the module's directory.

**Example: Running the AWX Playbook**

```bash
ansible-playbook awx/main.yml -i awx/inventory
```

Replace `awx` with the name of the module you wish to run.

### Creating New Modules

You can quickly scaffold a new Ansible module directory using the `prepare` script:

```bash
./prepare <APPNAME>
```

Replace `<APPNAME>` with the desired name for your new module (e.g., `my_new_app`). This script will create a new directory with a basic `inventory` file and a `main.yml` playbook, providing a starting point for your new automation.

## Contributing

Contributions are welcome! Please refer to the `CONTRIBUTING` file for guidelines on how to contribute to this Ansible Collection.