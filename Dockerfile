FROM php:7.4.3-apache

WORKDIR /var/www/html

# Upgrade + install driver
RUN apt-get update \
  && apt-get upgrade -y \
  && apt-get install git unzip -y \
  && docker-php-ext-install pdo pdo_mysql

# Add project files
COPY . ./

# Add config files
#// COPY ./.env.prod ./.env
#// COPY ./config/apache2/ /etc/apache2/sites-available/
#// RUN /etc/init.d/apache2 restart
# Or
RUN mv ./.env.prod ./.env \
  && mv -v ./config/apache2/* /etc/apache2/sites-available/ \
  && /etc/init.d/apache2 restart

# Install composer
COPY --from=composer /usr/bin/composer /usr/bin/composer
RUN composer update

#! composer: not found
# RUN curl -sS https://getcomposer.org/installer \
#   | php -- --install-dir=/usr/bin/composer --filename=composer \
#   | composer update