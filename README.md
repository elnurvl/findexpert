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

### Database
This application uses Neo4j graph database along with MYSQL to get connections between users in a more optimal way.
Refer [here](https://neo4j.com/news/how-much-faster-is-a-graph-database-really/) to see the benchmark comparison.

Install neo4j in your local machine before running the app. Refer to [this page](https://www.digitalocean.com/community/tutorials/how-to-install-and-configure-neo4j-on-ubuntu-20-04) for more information.

    sudo apt update
    sudo apt install apt-transport-https ca-certificates curl software-properties-common
    curl -fsSL https://debian.neo4j.com/neotechnology.gpg.key | sudo apt-key add -
    sudo add-apt-repository "deb https://debian.neo4j.com stable 4.1"
    sudo apt install neo4j
    sudo systemctl enable neo4j.service
Do not forget to fill in the db credentials in the `.env` file afterwards.

#### Neo4j Client
laudis/neo4j-php-client library is used to run CYPHER queries. However, it seems there is a bug in the library making it impossible to make changes on the database. The framework's HTTP client is used to execute those queries.

#### Why is this in the `additional` branch?
It took some time for me to find a reliable php client for the graph database. Despite I still had issues with it, it is the one library I found that is still maintained. 
### URL Shortener
Cutt.ly is a simple URL shortening service. Make sure you signed up in their website and acquired an API key. Add the API key to the `.env` file.
#### Why is this in the `additional` branch?
At the time of writing the code goo.gl service was shut down. Therefore, I had to find a simple and free alternative before starting to work on this feature.

## Planned features
- [ ] Users can remove friends
- [ ] Users can fetch topics manually in case there was a change in their websites, or a failure in previous attempts
- [ ] Users can update the URL shortenings for their websites
