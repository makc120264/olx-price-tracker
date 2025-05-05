#!/bin/bash

# Запуск cron в фоне
service cron start

# Запуск Apache в foreground
apache2-foreground
