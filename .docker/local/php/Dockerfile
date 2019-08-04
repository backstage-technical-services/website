FROM registry.gitlab.com/backstage-technical-services/infra/php:7.2

# Override the default script to allow PHP FPM to run as root
RUN echo "php-fpm -R | less +F -f ${LOG_STREAM}" > /usr/bin/docker-php

VOLUME /var/www