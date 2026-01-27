web: vendor/bin/frankenphp php-server --port=$PORT --root=public/
worker: php artisan queue:work --tries=2 --timeout=120 --sleep=3
