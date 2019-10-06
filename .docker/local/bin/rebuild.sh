#!/usr/bin/env bash
docker-compose stop site && \
docker-compose build --build-arg USER_ID=$(id -u "${USER}") --build-arg GROUP_ID=$(id -g "${USER}") site