#!/bin/bash

# We need to install dependencies only for Docker
[[ ! -e /.dockerenv ]] && exit 0

set -xe

# Install git (the php image doesn't have it) which is required by composer, and zip
apt-get update -yqq
apt-get install git unzip zlib1g-dev libzip-dev -yqq
docker-php-ext-install zip

# Install xdebug
pecl install xdebug
docker-php-ext-enable xdebug

# Install composer
curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
