#!/usr/bin/bash

ansible-playbook -i inventory install.yml -u root
ansible-playbook -i inventory user.yml -u root
ansible-playbook -i inventory environment.yml -u root
exit 0
