FROM registry.gitlab.com/backstage-technical-services/infra/php:7.2

ARG USER_ID
ARG GROUP_ID

# Recreate the www-data user and group to match that of the host machine's user
RUN if [ ${USER_ID:-0} -ne 0 ] && [ ${GROUP_ID:-0} -ne 0 ]; then \
    deluser --remove-home www-data && \
    if getent group www-data ; then delgroup www-data; fi && \
    addgroup --gid ${GROUP_ID} www-data && \
    adduser -D -u ${USER_ID} -G www-data www-data \
;fi

# Fix permissions for all nginx/PHP directories
RUN chown -R www-data:www-data \
    /etc/nginx \
    /var/lib/nginx \
    /var/log/nginx \
    /usr/lib/nginx \
    /run/nginx \
    /var/tmp/nginx \
    /usr/local/lib/php \
    /usr/local/etc/php/ \
    /usr/local/etc/php*

USER www-data

VOLUME /var/www