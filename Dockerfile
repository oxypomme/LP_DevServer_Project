FROM php:7.4.3-apache

WORKDIR /var/www/html

# Upgrade + install driver
RUN apt update \
  && apt upgrade -y \
  && docker-php-ext-install mysqli

# Add project files
ADD ./.env.prod ./.env
ADD . ./

EXPOSE 80