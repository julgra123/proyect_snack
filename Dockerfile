
FROM php:8.2-apache

# Instala las extensiones necesarias para conectar PHP con MySQL.
RUN docker-php-ext-install pdo pdo_mysql mysqli

# Activa la configuración de Apache.
RUN a2enmod rewrite

# Copia los archivos del sistema al servidor.
COPY . /var/www/html/

# Configura Apache para escuchar en el puerto de Render.
RUN sed -i 's/Listen 80/Listen 10000/' /etc/apache2/ports.conf \
    && sed -i 's/:80>/:10000>/' /etc/apache2/sites-available/000-default.conf

# Permite que PHP trabaje con la carpeta de imágenes subidas.
RUN chown -R www-data:www-data /var/www/html/uploads

EXPOSE 10000

CMD ["apache2-foreground"]
