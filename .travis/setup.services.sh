#!/usr/bin/env bash

docker run --name redis -p 6379:6379 -d redis &
wait

# Check if redis container is running
docker ps -a

# Check if redis is running
netstat -tunlp | grep 6379
