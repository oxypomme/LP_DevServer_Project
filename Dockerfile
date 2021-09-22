FROM php:7.4.3-apache

WORKDIR /var/www/html

# Upgrade + install driver
RUN apt update \
  && apt upgrade -y \
  && docker-php-ext-install mysqli

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
#// COPY --from=composer /usr/bin/composer /usr/bin/composer
#// RUN composer update
# Or
RUN curl -sS https://getcomposer.org/installer \
  | php -- --install-dir=/usr/bin/composer --filename=composer \
  | composer update

EXPOSE 80