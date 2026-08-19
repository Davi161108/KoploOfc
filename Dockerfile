FROM php:8.2-apache

# Instala a extensão mysqli para o PHP conectar com o MySQL
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Habilita o módulo de reescrita do Apache
RUN a2enmod rewrite

# Copia todos os arquivos da API para o diretório web do servidor
COPY . /var/www/html/

EXPOSE 80
