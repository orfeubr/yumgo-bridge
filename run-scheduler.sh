#!/bin/bash
cd /var/www/restaurante
php artisan schedule:run >> /dev/null 2>&1
