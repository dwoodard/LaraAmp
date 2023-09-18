#!/bin/bash

if [ "${1:-1}" == 'down' ]; then
  cd laradock || exit
  docker-compose down
  cd ..
  exit 0
fi

export COMPOSER_ALLOW_SUPERUSER=1

echo "local $(pwd)"
echo "--- php: $(php -r 'echo PHP_VERSION;') ---"
echo "--- composer: $(composer -V | awk '{ print $3 }') ---"
echo "--- npm: $(npm -v || echo 'run: npm install') ---"
echo "--- node: $(node -v || echo 'run: node install') ---"

#check permissions
chmod -R 777 storage

#  LOCAL
if [ "${1:-1}" == 'build' ]; then
  cd laradock || exit
  docker-compose build \
    nginx \
    mysql \
    php-worker \
    laravel-horizon \
    mailhog \
    phpmyadmin
else
  cd laradock || exit
  docker-compose up -d \
    nginx \
    mysql \
    php-worker \
    laravel-horizon \
    mailhog \
    phpmyadmin
  cd ..

  echo 'Website: http://localhost'
  echo 'Phpmyadmin: http://localhost:8081 root:root'
  echo 'Redis: http://localhost:9987 laradock:laradock'
fi

#check composer packages are installed
if [ ! -d "vendor" ]; then
  docker exec -it laradock-workspace-1 sh -c 'composer -n install;'
else
  echo 'composer was installed'
fi

# check if .env set
if [ ! -f ".env" ]; then
  docker exec -it laradock-workspace-1 sh -c "php artisan key:generate"
fi

#check npm packages are installed
if [ ! -d "node_modules" ]; then
  echo 'No dependencies installed. Trying to run npm install.'
  docker exec -it laradock-workspace-1 sh -c "npm install"
else
  echo 'npm was installed'
fi

#run migrations
if [ "${1:-1}" == 'fresh' ]; then
  docker exec -it laradock-workspace-1 sh -c "php artisan migrate:fresh"
else
  docker exec -it laradock-workspace-1 sh -c "php artisan migrate"
fi

$SHELL
