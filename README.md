# Requirements

- make
- docker

# Configuration

Copy the .env.dist file to .env.local

<b>Docker</b><br/>
- change APP_ENV to 'prod'

<b>Non Docker</b><br/>
- change APP_ENV to 'prod'
- change DATABASE_URL to the used database

# Running

Run the command `make up`<br/>
This is building the docker containers and preloading the database.

The webserver is running under port 8000.

# Testing

Run the command `make test`<br/>
This is running phpstan and the symfony tests

# Postman

There is a Postman collection for all endpoints under `shopping-cart.postman_collection.json`
