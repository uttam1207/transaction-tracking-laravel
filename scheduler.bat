@echo off
:: Laravel Scheduler — run every minute via Windows Task Scheduler
:: Task Scheduler should call this file every 1 minute.

cd /d D:\xampp\htdocs\Asdairy\transaction-tracking-laravel
D:\xampp\php\php artisan schedule:run >> D:\xampp\htdocs\Asdairy\transaction-tracking-laravel\storage\logs\scheduler-windows.log 2>&1
