@echo off
echo Remove Server Mounts
echo ====================
net use W: /delete
net use X: /delete
net use Y: /delete
net use Z: /delete

echo Reset Server Mounts
echo ===================
net use W: \\192.168.2.x\downloads
net use X: \\192.168.2.x\Backups
net use Y: \\192.168.2.x\Media
net use Z: \\192.168.2.x\Warez
