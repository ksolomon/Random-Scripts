#!/usr/bin/env bash

# etcbak - Backup Proxmox /etc folder

# Get the current date and time for file names
CURRENTDATE=`date +"%m.%d.%Y"`

# Archive /etc folder to your backup storage folder
tar czvf /path/to/proxmox/backups/proxmox_etc_backup_${CURRENTDATE}.tgz /etc | tee /var/log/etc_backup_log_${CURRENTDATE}.log

