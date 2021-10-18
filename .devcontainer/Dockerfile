FROM mcr.microsoft.com/vscode/devcontainers/php:7

WORKDIR /workspace

# Install composer
COPY --from=composer /usr/bin/composer /usr/bin/composer

ENV DEBIAN_FRONTEND=noninteractive

RUN composer install \
	&& npm i
