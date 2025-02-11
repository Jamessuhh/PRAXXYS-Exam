for backend
First, install Composer dependencies:
composer install
Create a copy of the .env file:
cp .env.example .env
Generate an application key:
php artisan key:generate
to refresh the database
php artisan migrate:fresh --seed
Then you should be able to run:
php artisan serve

---------------------------------------------------------------------------

for frontend
Install Node.js dependencies:
npm install
Create a .env file for your frontend 
cp .env.example .env
Then you should be able to run:
npm run dev

---------------------------------------------------------------------------

if getting cors blocked
go to cors.php
Line 22- 'allowed_origins' => [env('FRONTEND_URL', 'http://localhost:5173')],
