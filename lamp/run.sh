#!/bin/bash

echo "Update all packages, before installation."
ansible-playbook -i inventory ../update/main.yml -u root

echo "Install basic toolchain."
ansible-playbook -i inventory basics.yml -u root

echo "Install Apache 2.x."
ansible-playbook -i inventory apache2.yml -u root

echo "Install MySQL 8.x."
ansible-playbook -i inventory mysql.yml -u root

echo "Install PHP 8.1 and install PHP MySQL Module."
ansible-playbook -i inventory php8.yml -u root

echo "Install phpmyadmin."
ansible-playbook -i inventory phpmyadmin.yml -u root

echo "Install Lets Encrypt."
ansible-playbook -i inventory letsencrypt.yml -u root

echo "Done."
exit 0
