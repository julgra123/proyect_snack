FROM php:8.2-apache

RUN docker-php-ext-install pdo_mysql

RUN a2enmod rewrite

# Crear o identificar el grupo 1000
RUN if getent group 1000 >/dev/null; then \
        GROUP_NAME="$(getent group 1000 | cut -d: -f1)"; \
    else \
        groupadd -g 1000 render-secrets; \
        GROUP_NAME=render-secrets; \
    fi && \
    usermod -aG "$GROUP_NAME" www-data

WORKDIR /var/www/html

COPY . /var/www/html/

RUN mkdir -p /var/www/html/uploads \
    && chown -R www-data:www-data /var/www/html/uploads \
    && chmod -R 775 /var/www/html/uploads

EXPOSE 80
