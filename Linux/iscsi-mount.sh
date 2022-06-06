#!/usr/bin/env bash
echo "Login to iscsi target"
iscsiadm -m node --login

echo "iscsi session"
iscsiadm -m session -o show

echo "Mount /storage"
mount /storage/

echo "Restart Samba and Plex"
systemctl restart smbd
systemctl restart plexmediaserver.service
