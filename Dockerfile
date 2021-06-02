FROM php:7.2.4-apache

LABEL "com.uestudio.vendor"="laporteriavertical"
LABEL version="0.0.2"
LABEL description="docker for cluster laporteriavertical"

# Install dependencies
RUN apt-get update -y
RUN apt-get install -y git zip unzip
RUN apt-get install -y vim
RUN docker-php-ext-install pdo_mysql

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Install app
ADD . /var/www/html
COPY src/env.php.dist /var/www/html/src/env.php

WORKDIR /var/www/html

RUN composer install
RUN composer dump-autoload
RUN apache2 -v

# Enable apache mods.
RUN a2enmod rewrite
RUN a2enmod deflate
RUN a2enmod headers

# Manually set up the apache environment variables
ENV APACHE_RUN_USER www-data
ENV APACHE_RUN_GROUP www-data
ENV APACHE_LOG_DIR /var/log/apache2
ENV APACHE_LOCK_DIR /var/lock/apache2
ENV APACHE_PID_FILE /var/run/apache2.pid

# Update the default apache site with the config we created.
ADD config/apache/000-default.conf /etc/apache2/sites-enabled/000-default.conf

RUN service apache2 restart

EXPOSE 80

# By default start up apache in the foreground, override with /bin/bash for interative.
CMD /usr/sbin/apache2ctl -D FOREGROUND
