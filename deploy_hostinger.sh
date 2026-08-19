#!/bin/bash

# ==============================================================================
# Hostinger Shared Hosting Deployment Script for Social Inbox Automation SaaS
# ==============================================================================

echo "Starting deployment for Hostinger Shared Hosting..."

# 1. Install Composer dependencies (no dev packages)
composer install --no-dev --optimize-autoloader

# 2. Run Database Migrations
php artisan migrate --force

# 3. Cache configuration and routes for maximum performance on shared hosting
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 4. Storage Link
php artisan storage:link

echo "Deployment complete! Ensure Hostinger Cron Job is configured:"
echo "* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1"
