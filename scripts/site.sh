#!/usr/bin/env bash
set -eo pipefail
readonly rootDir="$(dirname "$(realpath "${0}")")/.."
# adds support for compose v2
if command -v docker-compose >/dev/null 2>&1; then
  readonly composeCmd="docker-compose"
else
  readonly composeCmd="docker compose"
fi


# Ensure that Docker is running...
if ! docker info >/dev/null 2>&1; then
  echo "Docker is not running." >&2
  exit 1
fi

function _showHelp() {
  cat <<EOF
###################
##  BTS Website  ##
###################

This scripts provides some helpful utility functions for interacting
with the local Docker container, to aid with local development.

Commands:
  docker [OPTIONS] COMMAND
    Run any normal docker-compose command.
  start
    Start the site and dependencies.
  stop
    Stop the site and dependencies.
  reset
    Stop the site and dependencies, clears the database, and restarts the site and dependencies.
  status
    View the status of the docker containers.
  rebuild
    Pull the latest base image and rebuild the site's docker image
    and container.
  exec COMMAND
    Run any command inside the container.
  composer [OPTIONS] COMMAND
    Run any composer command.
  yarn [OPTIONS] COMMAND
    Run any yarn script (alias for yarn run).
  install [--update]
    Install both the PHP and JavaScript dependencies. Use the --update
    flag to update the dependencies to their latest versions.
  artisan COMMAND
    Run any artisan command.
  build-assets
    Build the JavaScript and CSS assets.
  watch-assets
    Build the JavaScript and CSS assets, and rebuild on any changes.
  update
    Update your local copy of the site, including installing any new
    dependencies, re-building the assets and running any new migrations.
  help
    Show this information.
EOF
}

function _docker() {
  $composeCmd -f "$rootDir/docker-compose.yml" "$@"
}

function _start() {
  _docker up -d
}

function _stop() {
  _docker stop
}

function _reset() {
  _docker down
  _start
}

function _requireRunning() {
  _docker ps | grep site &>/dev/null || {
    echo "Site not running. Run start and try again." >&2
    exit 2
  }
}

function _status() {
  _docker ps
}

function _rebuild() {
  # Stop the network
  _stop

  # Pull the base image
  _docker pull site

  # Rebuild the site
  _docker build \
    --build-arg="USER_ID=$(id -u "${USER}")" \
    --build-arg="GROUP_ID=$(id -g "${USER}")" \
    site

  # Start
  _start
}

function _exec() {
  _requireRunning
  _docker exec --user www-data:www-data site "$@"
}

function _composer() {
  _exec composer "$@"
}

function _artisan() {
  _exec php artisan "$@"
}

function _yarn() {
  _exec yarn run "$@"
}

function _install() {
  _requireRunning

  if [[ "${1}" == "--update" ]]; then
    _composer update
    _exec yarn update
  else
    _composer install
    _exec yarn
  fi
}

function _buildAssets() {
  _requireRunning

  _yarn dev
}

function _watchAssets() {
  _requireRunning

  _yarn watch
}

function _test() {
  _requireRunning

  _artisan test
}

function _update() {
  _requireRunning

  pushd "${rootDir}" &>/dev/null

  if [[ ! $(ssh-keygen -F github.com) ]]; then
      echo "=> Adding SSH keys for github.com"
      ssh-keyscan -H github.com >> ~/.ssh/known_hosts
  fi

  echo "=> Updating your local copy ..."
  git pull -ff-only &>/dev/null

  echo "=> Installing dependencies ..."
  _install &>/dev/null

  echo "=> Building assets ..."
  _buildAssets &>/dev/null

  echo "=> Running migrations ..."
  _artisan migrate

  echo "=> Clearing the cache ..."
  _artisan cache:clear &>/dev/null
  _artisan view:clear &>/dev/null
  _artisan config:clear &>/dev/null

  echo "=> Done."

  popd &>/dev/null
}

function _logs() {
  docker compose logs -f site
}

readonly cmd="${1}"
shift

case $cmd in
docker)
  _docker "$@"
  ;;
start)
  _start
  ;;
stop)
  _stop
  ;;
reset)
  _reset
  ;;
status)
  _status
  ;;
rebuild)
  _rebuild
  ;;
logs)
  _logs
  ;;
exec)
  _exec "$@"
  ;;
composer)
  _composer "$@"
  ;;
yarn)
  _composer "$@"
  ;;
install)
  _install "$@"
  ;;
artisan)
  _artisan "$@"
  ;;
build-assets)
  _buildAssets
  ;;
watch-assets)
  _watchAssets
  ;;
test)
  _test
  ;;
update)
  _update
  ;;
help)
  _showHelp
  ;;
*)
  _showHelp
  ;;
esac
