Setup Steps

composer install
copy .env.example to .env
php artisan key:generate
configure DB in .env
php artisan migrate
php artisan serve
