#!/usr/bin/env sh

# linkcert: Link newest certificate files to a directory for a remote machine to copy.
#
# This script is to be run on the HOST machine.  It is based on Nginx Proxy Manager running in an alpine LXC container on Proxmox.
#
# This will link the newest pem files in the LetsEncrypt directory to a specific folder the target script will reference.  Change the lines below to match up with your specific configuration.

# Link Cert
ln -s `ls -rt /path/to/letsencrypt/archive/site/cert* | tail -n1` /path/to/certs/cert.pem

# Likn Chain
ln -s `ls -rt /path/to/letsencrypt/archive/site/chain* | tail -n1` /path/to/certs/chain.pem

# Link Key
ln -s `ls -rt /path/to/letsencrypt/archive/site/priv* | tail -n1` /path/to/certs/key.pem
