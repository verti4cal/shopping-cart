# Requirements

- make
- docker

# Configuration

- copy .env.dist to .env.prod.local -> change APP_ENV to prod
- copy .env.dist to .env.dev.local

# Running

The webserver is running under port 8000.

<b>Prod</b><br/>
Run the command `make up`<br/>
This is building the docker containers and preloading the database.<br>
<b>Rebuild:</b><br/>
Run the command `make build`

<b>Dev</b><br/>
Run the command `make dev`<br/>
This is building the docker containers and preloading the database.<br>
<b>Rebuild:</b><br/>
Run the command `make builddev`

<b>Hint</b><br/>
When switching between dev and prod, a rebuild has to be done.

# Testing

Run the Dev containers<br/>
Run the command `make test`<br/>
This is running phpstan and the symfony tests<br/>
Coverage reports are available under `http://localhost:8000/coverage/index.html`

# Postman

There is a Postman collection for all endpoints under `shopping-cart.postman_collection.json`
