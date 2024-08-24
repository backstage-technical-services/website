FROM ghcr.io/backstage-technical-services/php-docker:8.2 AS builder

USER root

# Install yarn and composer
RUN apk update && apk upgrade && apk add --update npm \
    && npm install --global yarn \
    && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

USER www-data

# Copy source
COPY --chown=www-data:www-data . ./

# Install PHP dependencies and generate autoloader
RUN composer install --prefer-dist --no-dev --no-scripts

# Build assets
RUN yarn install \
    && yarn run production

FROM ghcr.io/backstage-technical-services/php-docker:8.2

# Copy the source code
COPY --chown=www-data:www-data . .

# Copy the dependencies and prebuilt files from the builder
COPY --chown=www-data:www-data --from=builder /var/www/public/ ./public/
COPY --chown=www-data:www-data --from=builder /var/www/vendor/ ./vendor/

#  Declare the volumes
VOLUME /var/www/public/images/profiles
VOLUME /var/www/resources/resources
VOLUME /var/www/resources/elections

# Append the extra supervisord config
COPY .docker/conf.d/supervisord.conf /tmp/supervisord.conf.extra
RUN cat /tmp/supervisord.conf.extra >> /etc/supervisord.conf

# Configure the entrypoint
COPY --chown=www-data:www-data .docker/bin/entrypoint.sh /usr/local/bin/entrypoint
RUN chmod +x /usr/local/bin/entrypoint
ENTRYPOINT ["/usr/local/bin/entrypoint"]
CMD ["supervisord", "-n", "-c", "/etc/supervisord.conf"]
