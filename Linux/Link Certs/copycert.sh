#!/usr/bin/env bash

# copycert: Copy certificate files from a remote machine to a target machine.
#
# This script is to be run on the TARGET machine.
#
# This will change to the directory where your setup expects to find the certificate files.  Change the lines below to match up with your specific configuration.

# Change to cert directory
cd /etc/ssl/<certdir>

# Copy cert files from NPM
scp <user>@address:/path/to/certs/*.pem ./

# Restart Apache to pick up new cert
systemctl restart apache2
