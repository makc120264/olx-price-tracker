FROM php:8.2-apache

# Installing dependencies
RUN apt-get update && apt-get install -y cron zip unzip libxml2-dev && docker-php-ext-install dom

# Copy the code and configs
COPY . /app
WORKDIR /app

# Copy the cron
COPY crontab/crontab.conf /etc/cron.d/price-checker
RUN chmod 0644 /etc/cron.d/price-checker && crontab /etc/cron.d/price-checker

# PHP settings
COPY .docker/php.ini /usr/local/etc/php/

# Entry point
COPY entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

EXPOSE 80

CMD ["/entrypoint.sh"]
