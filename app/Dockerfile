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

# Installation de Composer (gestion des dépendances PHP, ex: mongodb/mongodb)
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Le DocumentRoot pointe vers public/ : seul ce dossier est accessible
# depuis le navigateur. config/, src/ restent invisibles depuis une URL.
COPY apache-vhost.conf /etc/apache2/sites-available/000-default.conf

WORKDIR /var/www/html

# Le code source est monté en volume depuis docker-compose.yml
# -> composer install se lance manuellement après "docker compose up"
# (voir README.md), car le volume écrase le contenu copié au build.
