# Record Shop

Requirements: 
 - GNU make
 - Docker 19.03.0+ 
 - Docker Compose, 
 - Port 8080 and 3310 free

## Infrastructure
 - Separate Docker containers for Nginx, PHP-FPM and MySQL

 - `Docker Compose` is used to manage the containers together (docker/docker-compose-dev.yml)

 - `PHP` version 7.4 running on Alpine

 - `Nginx` 1.19 running on Alpine

 - `REST Api` is running at http://localhost:8080/api

 - `MySQL` 5.7 is running at localhost:3310 
    - username and password are `root`
    - databases `record-shop` and `record-shop-test `

 - `Xdebug` is running on port 9001 (additional config in docker/php-fpm/dev.xdebug.ini)

## Application
 - Symfony 5.1
 
 - Doctrine for persistence

 - PHPUnit for tests

 - PHPCS for static analysis using PSR-12

 - OpenApi documentation generated through Annotations (NelmioApiDocBundle)



## OpenAPI Documentation
 - UI: http://localhost:8080/api/doc
 - JSON: http://localhost:8080/api/doc.json
 
## Endpoints
 - `/api/records`
    - GET and POST
 - `/api/records/{id}`
    - GET, PUT and DELETE

## Running the application
GNU `make` is being used for shortcuts of Docker and Docker Compose commands.

For starting the application in the first time run the following:

`make up-build-dev`

This is a list of some additional commands, more can be found in the `Makefile` 
 - `up` for just starting the containers
 - `down` for applying compose down command
 - `php-sh` to access the PHP container
 - `tests` to run the tests
 - `logs` to print the docker logs