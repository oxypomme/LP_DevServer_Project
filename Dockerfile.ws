FROM php:7.4.3-alpine AS builder

WORKDIR /build

# Add project files
COPY . ./
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
  && mv php/ public/ vendor/ .env ./*.php ./composer.json /app/

# ====

FROM php:7.4.24-alpine

WORKDIR /app

# Add project files
COPY --from=builder /app ./

RUN apk update \
  && apk upgrade -U -a \
  && docker-php-ext-install pdo pdo_mysql

# Run WS Server
CMD php /app/public/socket.php