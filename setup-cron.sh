#!/bin/bash
# ASDairy Laravel Scheduler — Production Cron Setup
# Run once on the server: bash setup-cron.sh

APP_DIR="/var/www/transaction-tracking-laravel"
PHP_BIN=$(which php8.2 2>/dev/null || which php8.1 2>/dev/null || which php 2>/dev/null)
CRON_LINE="* * * * * $PHP_BIN $APP_DIR/artisan schedule:run >> /dev/null 2>&1"
CRON_USER=$(whoami)

echo "PHP binary : $PHP_BIN"
echo "App path   : $APP_DIR"
echo "Cron user  : $CRON_USER"
echo ""

# Check if cron entry already exists
(crontab -l 2>/dev/null | grep -q "schedule:run") && {
    echo "Cron already set up:"
    crontab -l | grep "schedule:run"
    exit 0
}

# Append the new cron entry
(crontab -l 2>/dev/null; echo "$CRON_LINE") | crontab -

echo "Cron added successfully:"
crontab -l | grep "schedule:run"
echo ""
echo "Test with: $PHP_BIN $APP_DIR/artisan schedule:list"
