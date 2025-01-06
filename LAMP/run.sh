#!/bin/bash

echo "Update all packages, before installation."
sudo ansible-playbook -i inventory ../Update/main.yml

echo "Install basic toolchain."
sudo ansible-playbook -i inventory basics.yml

echo "Install Apache 2.x."
sudo ansible-playbook -i inventory apache2.yml

echo "Install MySQL 8.x."
sudo ansible-playbook -i inventory mysql.yml

echo "Install PHP 8.1 and install PHP MySQL Module."
sudo ansible-playbook -i inventory php8.yml

echo "Install phpmyadmin."
sudo ansible-playbook -i inventory phpmyadmin.yml

echo "Install Lets Encrypt."
sudo ansible-playbook -i inventory letsencrypt.yml

echo "Done."
exit 0
