#!/usr/bin/env bash
set -euo pipefail

if [[ ! -f .env ]]; then
  echo "ERROR: .env file missing" >&2
  exit 1
fi

if [[ "${1:-}" = "" ]]; then
  echo "ERROR: missing name of env variable to set (1st argument)"
  exit 1
fi

if [[ "${2:-}" = "" ]]; then
  echo "ERROR: missing new value of env variable (2nd argument)"
  exit 1
fi

echo "$1=$2" >> .env
echo "Written $1 to .env"
