#!/usr/bin/env bash
php-fpm -R -D

nginx -g 'daemon off;'