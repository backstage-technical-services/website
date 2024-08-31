FROM ghcr.io/backstage-technical-services/php-docker:8.2

ARG USER_ID
ARG GROUP_ID

# Install additional dependencies
RUN apk update && apk upgrade && apk add --update \
    git \
    nodejs \
    npm \
    mysql-client

# Install composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Install yarn
RUN npm install -g yarn

# Recreate the www-data user and group to match that of the host machine's user
RUN if [ ${USER_ID:-0} -ne 0 ] && [ ${GROUP_ID:-0} -ne 0 ]; then \
    deluser --remove-home www-data && \
    if getent group www-data ; then delgroup www-data; fi && \
    if getent group ${GROUP_ID}; then delgroup $(getent group ${GROUP_ID}); fi && \
    addgroup --gid ${GROUP_ID} www-data && \
    adduser -D -u ${USER_ID} -G www-data www-data \
;fi

# Fix permissions for all nginx/PHP directories
RUN mkdir -p \
    /etc/nginx \
    /var/lib/nginx \
    /var/log/nginx \
    /usr/lib/nginx \
    /run/nginx \
    /var/tmp/nginx \
    /usr/local/lib/php \
    /usr/local/etc/php
RUN chown -R www-data:www-data \
    /etc/nginx \
    /var/log/nginx \
    /usr/lib/nginx \
    /run/nginx \
    /var/tmp/nginx \
    /usr/local/lib/php \
    /usr/local/etc/php/ \
    /usr/local/etc/php*

VOLUME /var/www
