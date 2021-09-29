FROM node:lts-alpine AS client-builder

WORKDIR /build

# Add project files
COPY . ./
# Upgrade
RUN apk update \ 
  && apk upgrade -U -a \
  # Build client
  && if [ -f .env.prod ]; then mv -f .env.prod .env; fi \
  && npm i \
  && export NODE_ENV=production \
  && npm run build

# ====

FROM php:7.4.3-alpine AS builder

WORKDIR /build

# Add project files
COPY --from=client-builder /build/ ./
# Install composer
COPY --from=composer /usr/bin/composer /usr/bin/composer
# Upgrade
RUN apk update \
  && apk upgrade -U -a \
  && apk add git unzip --no-cache \
  # Install dependencies
  && composer install --no-dev --optimize-autoloader \
  # Make it fetchable
  && mkdir /app \
  && mv config/ dist/ php/ public/ vendor/ .env ./*.php ./composer.json /app/

# ====

FROM php:7.4.3-apache

WORKDIR /var/www/html

# Add project files
COPY --from=builder /app ./

# Upgrade + install driver
RUN apt-get update \
  && apt-get upgrade -y \
  && docker-php-ext-install pdo pdo_mysql \
  # Add config files
  && mv -v ./config/apache2/* /etc/apache2/sites-available/

# Run server
CMD /etc/init.d/apache2 restart ; php ./public/socket.php