#!/usr/bin/env bash

echo 'Stop and remove existing container'
docker stop portainer
docker rm portainer
echo ''

echo 'Pull new version'
docker pull cr.portainer.io/portainer/portainer-ce:latest
echo ''

echo 'Start new version'
docker run -d -p 8000:8000 -p 9443:9443 --name=portainer --restart=always -v /var/run/docker.sock:/var/run/docker.sock -v portainder_data:/data cr.portainer.io/portainer/portainer-ce:latest
