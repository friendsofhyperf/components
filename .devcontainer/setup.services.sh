#!/usr/bin/env bash

docker run --name mysql75 -d mysql:5.7
docker exec -it mysql75 mysql -uroot -e "FLUSH PRIVILEGES;"
docker exec -it mysql75 mysql -uroot -e "create database hyperf;"