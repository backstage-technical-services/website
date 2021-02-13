FROM docker.pkg.github.com/backstage-technical-services/php-docker/php:8.0

# Copy dependency files
COPY composer.json composer.lock ./
COPY yarn.lock ./

# Install dependencies
RUN composer install --prefer-dist --no-dev --no-scripts --no-autoloader
RUN yarn install

# Copy the source code
COPY . ./

# Generate the autoloader
RUN composer dump-autoload

# Generate the assets
RUN yarn run production

# Fix permissions
RUN chown -R www-data:www-data /var/www

# Run as user
USER www-data

# Set up the resource volumes
VOLUME /var/www/resources/resources
VOLUME /var/www/resources/elections
