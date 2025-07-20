#!/usr/bin/bash

HOST=$1

ssh-copy-id -i ~/.ssh/id_ed25519.pub -o StrictHostKeyChecking=no root@$HOST
