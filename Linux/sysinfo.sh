#!/bin/sh

UPTIME_DAYS=$(expr `cat /proc/uptime | cut -d '.' -f1` % 31556926 / 86400)
UPTIME_HOURS=$(expr `cat /proc/uptime | cut -d '.' -f1` % 31556926 % 86400 / 3600)
UPTIME_MINUTES=$(expr `cat /proc/uptime | cut -d '.' -f1` % 31556926 % 86400 % 3600 / 60)

cat << EOF
%------------------------------- SERVER INFO --------------------------------%
Name: Aduril WSL2
Uptime: $UPTIME_DAYS days, $UPTIME_HOURS hours, $UPTIME_MINUTES minutes
CPU: `cat /proc/cpuinfo | grep 'model name' | head -1 | cut -d':' -f2`
Memory: `free -m | head -n 2 | tail -n 1 | awk {'print $2'}`M
Swap: `free -m | tail -n 1 | awk {'print $2'}`M
Disk: `df -h / | awk '{ a = $2 } END { print a }'`
Distro: `lsb_release -s -d` with `uname -r`

CPU Load: `cat /proc/loadavg | awk '{print $1 ", " $2 ", " $3}'`
Free Memory: `free -m | head -n 2 | tail -n 1 | awk {'print $4'}`M
Free Swap: `free -m | tail -n 1 | awk {'print $4'}`M
Free Disk: `df -h / | awk '{ a = $2 } END { print a }'`

Local Address: `/sbin/ip -o -4 addr list eth0 | awk '{print $4}' | cut -d/ -f1`
Public Address: `dig +short myip.opendns.com @resolver1.opendns.com`
EOF
