#!/bin/bash

cd /app
composer install
supervisord -c /etc/supervisord.conf