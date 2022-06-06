#!/bin/sh

echo
echo '-----Kill Proxmox VM-----'

echo
echo '---Existing locks---'
qm unlock $1
ls -l /run/lock/qemu-server
rm -f /run/lock/qemu-server/lock-$1.conf
qm unlock $1

echo

echo '---Remaining locks---'
ls -l /run/lock/qemu-server

echo '---Stop VM---'
qm stop $1 && qm status $1