#!/bin/bash

# Запуск cron в фоне
service cron start

# Запуск server в foreground
nginx -g "daemon off;"
