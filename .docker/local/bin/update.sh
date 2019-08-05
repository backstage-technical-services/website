#!/usr/bin/env bash
echo "Updating your local copy ..."
git pull &> /dev/null

echo "Updating the dependencies ..."
docker exec bts_php sh -c "composer install && yarn install" &> /dev/null

echo "Compiling the assets ..."
docker exec bts_php sh -c "yarn dev" &> /dev/null

echo "Running any migrations ..."
docker exec bts_php sh -c "php artisan migrate" &> /dev/null

docker exec bts_php sh -c "php artisan cache:clear" &> /dev/null

echo "Done."
