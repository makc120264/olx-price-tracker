FROM php:8.2-apache

# Установка зависимостей
RUN apt-get update && apt-get install -y cron zip unzip libxml2-dev && docker-php-ext-install dom

# Включаем mod_rewrite
RUN a2enmod rewrite

# Копируем код и конфиги
COPY . /app
WORKDIR /app

# Копируем крон
COPY crontab/crontab.conf /etc/cron.d/price-checker
RUN chmod 0644 /etc/cron.d/price-checker && crontab /etc/cron.d/price-checker

# Настройки PHP
COPY .docker/php.ini /usr/local/etc/php/

# Точка входа
COPY entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

EXPOSE 80

CMD ["/entrypoint.sh"]
