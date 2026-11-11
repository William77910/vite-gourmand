# Image de base : PHP 8.2 avec Apache intégré
FROM php:8.2-apache

# Extensions PHP nécessaires au projet
RUN docker-php-ext-install pdo pdo_mysql

# Extension MongoDB (nécessite pecl + dépendances système)
RUN apt-get update && apt-get install -y libssl-dev git unzip \
    && pecl install mongodb \
    && docker-php-ext-enable mongodb \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Active le module de réécriture d'URL Apache (routes propres)
RUN a2enmod rewrite

# Installation de Composer (gestion des dépendances PHP)
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Copie des fichiers de configuration (chemin relatif depuis la racine)
COPY app/apache-vhost.conf /etc/apache2/sites-available/000-default.conf
COPY app/php-uploads.ini /usr/local/etc/php/conf.d/uploads.ini

WORKDIR /var/www/html

# Copie du code source dans l'image
COPY app/ .

# Permissions pour le serveur web
RUN chown -R www-data:www-data /var/www/html
