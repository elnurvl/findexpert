This is an API service that can be consumed by an SPA or a mobile app.
The repository does not contain any code related to the front-end. See client repository if you are looking for the source of the SPA and the mobile app.

## Get Started
First, make sure you have a PHP 8.0 server, and a MYSQL database running (e.g. *XAMPP*, an all-in-one solution).
To set up the project in a local environment, first rename the `.env.example` file to `.env`. After filling in the db credentials in the `.env` file run the following commands one by one:

    composer install
    composer dump-autoload
    npm install
    php artisan key:generate
    php artisan migrate
    php artisan serve
## Additional features
Checkout into the `additional` branch if you want to see user connections in the search results and utilize a URL shortener.

## Planned features
- [ ] Users can remove friends
- [ ] Users can fetch topics manually in case there was a change in their websites, or a failure in previous attempts
- [ ] Users can update the URL shortenings for their websites
