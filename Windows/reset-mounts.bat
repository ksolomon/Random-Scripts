@echo off
echo Remove Server Mounts
echo ====================
net use M: /delete
net use N: /delete
net use O: /delete
net use P: /delete
net use Q: /delete
net use R: /delete
net use S: /delete
net use T: /delete
net use X: /delete
net use Y: /delete
net use Z: /delete

echo Reset Server Mounts
echo ===================
net use M: \\sshfs\xxxx@xxx.xxx.xx.xxx\..
net use N: \\sshfs\xxxxxxx@xxx.xxxxxxxxxxxxxx.xxx
net use O: \\10.0.0.e3\watched
net use P: \\10.0.0.e3\uploads
net use Q: \\10.0.0.e3\timelapse
net use R: \\10.0.0.x5\watched
net use S: \\10.0.0.x5\uploads
net use T: \\10.0.0.x5\timelapse
net use X: \\10.0.0.ha\config
net use Y: \\10.0.0.px\Media
net use Z: \\10.0.0.px\Backups
