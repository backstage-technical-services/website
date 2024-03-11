#!/usr/bin/env bash
set -eo pipefail

exitInvalidEnv=1

function _error() {
  echo "${1}" >&2
}

readonly repository="${1}"
readonly commit="${2}"
readonly siteRoot="${3}"
readonly siteRootPath="/var/www/${siteRoot}"

if [[ -z "${repository}" ]]; then
  _error "Cannot check repository out: no repository provided"
  exit $exitInvalidEnv
fi

if [[ -z "${commit}" ]]; then
  _error "Cannot check repository out: no commit hash provided"
  exit $exitInvalidEnv
fi

if [[ -z "${siteRoot}" ]]; then
  _error "Cannot check repository out: no site root provided"
  exit $exitInvalidEnv
fi

# Check out repository
echo "=> Cloning to ${siteRootPath}/${commit} ..."
ssh-keyscan -H github.com >>~/.ssh/known_hosts
rm -rf "${siteRootPath:?}/${commit}"
git clone "${repository}" "${siteRootPath}/${commit}"
pushd "${siteRootPath}/${commit}"

echo "=> Checking out ${commit} ..."
git checkout "${commit}"

# Dependencies
echo "=> Installing dependencies ..."
composer install --no-interaction --no-dev --prefer-dist
yarn install --no-dev --frozen-lockfile
echo "=> Building dependencies ..."
yarn prod

# Copy config
echo "=> Configuring application ..."
cp "${siteRootPath}/.env" .env

# Migrations
echo "=> Running migrations ..."
php artisan migrate --force --no-interaction

# Cache
echo "=> Configuring cache ..."
php artisan config:cache

# Symlink resources
echo "=> Configuring symlinks ..."
rm -rf "${siteRootPath}/${commit}/public/images/profiles"
ln -s "${siteRootPath}/storage/profiles" "${siteRootPath}/${commit}/public/images/profiles"
ln -s "${siteRootPath}/storage/elections" "${siteRootPath}/${commit}/resources/elections"
ln -s "${siteRootPath}/storage/resources" "${siteRootPath}/${commit}/resources/resources"

# Configuring permissions
echo "=> Configuring permissions ..."
chown -R forge:www-data "${siteRootPath}/${commit}"

# Switch over
echo "=> Switching over ..."
current="$(readlink -f "${siteRootPath}/active")"
rm "${siteRootPath}/active" && ln -s "${siteRootPath}/${commit}" "${siteRootPath}/active"
chown -R forge:www-data "${siteRootPath}/active"
rm -rf "${current}"

# Restart nginx
echo "=> Restarting nginx"
sudo service nginx reload

echo "=> Done."
