FROM registry.gitlab.com/backstage-technical-services/infra/php:7.2

# Override the default script to allow PHP FPM to run as root
COPY .docker/local/site/start.sh /usr/bin/start
RUN chmod +x /usr/bin/start

VOLUME /var/www