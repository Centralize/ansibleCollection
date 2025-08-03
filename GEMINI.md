# Gemini CLI Integration for Ansible Collection

This document outlines how the Gemini CLI can interact with and manage this Ansible Collection.

## Project Overview

This repository is an Ansible Collection, containing various Ansible Playbooks for automating system administration tasks and application deployments. Each top-level directory represents a distinct Ansible module (e.g., `awx`, `lamp`, `nextcloud`), typically containing:
- `inventory`: Defines target hosts.
- `main.yml`: The primary playbook.
- Supporting files (e.g., `templates`, `files`, `group_vars`).

## Key Scripts for Gemini CLI Interaction

- **`install.ansible`**: This script automates the installation of Ansible on Debian/Ubuntu systems.
  - **Usage**: `run_shell_command(command='./install.ansible', description='Install Ansible on the system.')`

- **`prepare <APPNAME>`**: This script scaffolds a new Ansible module directory. It creates a new directory named `<APPNAME>` with a basic `inventory` file and a `main.yml` playbook.
  - **Usage**: `run_shell_command(command='./prepare <APPNAME>', description='Create a new Ansible module directory.')`
  - **Example**: `run_shell_command(command='./prepare my_new_app', description='Create a new Ansible module directory for "my_new_app".')`

## General Interaction with Playbooks

To run a specific playbook, you would typically use the `ansible-playbook` command, specifying the `main.yml` file and the `inventory` file within the module's directory.

- **Example**: To run the `awx` playbook:
  `run_shell_command(command='ansible-playbook awx/main.yml -i awx/inventory', description='Run the AWX Ansible playbook.')`

## Common Tasks for Gemini CLI

- **Listing Playbooks**: Use `list_directory` to see available module directories.
- **Reading Playbook Content**: Use `read_file` to inspect `main.yml` or `inventory` files.
- **Modifying Playbooks**: Use `replace` or `write_file` to update playbook content.
- **Running Playbooks**: Use `run_shell_command` to execute `ansible-playbook` commands.
