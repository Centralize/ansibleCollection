#!/usr/bin/bash

INSTTYPE=$1

if [[ $INSTTYPE -eq "REPO" || $INSTTYPE -eq "repo" ]]; then
	ansible-playbook -i inventory setupRepo.yml -u root
	exit 0
fi

if [[ $INSTTYPE -eq "MASTER" || $INSTTYPE -eq "master" ]]; then
        ansible-playbook -i inventory setupMaster.yml -u root
        exit 0
fi

if [[ $INSTTYPE -eq "MINION" || $INSTTYPE -eq "minion" ]]; then
        ansible-playbook -i inventory setupMinion.yml -u root
        exit 0
fi

echo "Parametre is missing: try $0 REPO|MASTER|MINION"
exit 1
