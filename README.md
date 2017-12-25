# testAPI

####Instructions:

1. Clone the project
2. Run `composer install`
3. Rename the file .env.example to .env, set the DB connection on it.
4. Run the DB migrations with => `php artisan migrate`
5. If you want some seeded data, run the previous command with `--seed` option
6. Set the app key with => 'php artisan key:generate'
7. Set the JWT Token with => 'php artisan jwt:secret'
8. Generate a user with the command => `php artisan user:generate <email> --password=pasasword`
9. Launch your web server with => `php -S localhost:8000 -t public/`

####Notes:

* The API have Authentication via JWT.
* In order to do any HTTP Request you must add the Authorization Header with the value of the token returned  by the login route.
* All the routes in the API have 'api/v1/' as prefix.
* Go to the `web\routes.php' file to find all the routes available
* The Authorization header must be something like this -> `Authorization : Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vbG9jYWxob3N0OjgwMDAvYXBpL3YxL2F1dGgvbG9naW4iLCJpYXQiOjE0ODgwNDU2NzUsImV4cCI6MTQ4ODA0OTI3NSwibmJmIjoxNDg4MDQ1Njc1LCJqdGkiOiJTTUpXMllOS3FjZWJ6RWx5Iiwic3ViIjo2fQ.6PB5Rk07TWYj7tMSGR3xkRkTxCRndSsZ4V0rCWtOhdg` (Keep in mind that the Bearer keyword it's required.
* It's highly recommended to use the latest version of PHP i.e: 7.0
