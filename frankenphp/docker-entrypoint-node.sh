#!/bin/sh
set -e

if [ ! -d "node_modules" ]; then
  echo "Node modules not found, running npm install..."
  npm install
fi

exec "$@"
