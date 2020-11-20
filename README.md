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

 - All that is set for the infrastructure was only configured to enable a `development environment`. In no way this is 
 supposed to be used in production/public facing environments. One of the examples of bad practices is the hard coding 
 of passwords in the source code (env files and docker-compose).

## Application
 - Symfony 5.1
 
 - Doctrine for persistence

 - PHPUnit for tests

 - PHPCS for static analysis using PSR-12

 - OpenApi documentation generated through Annotations (NelmioApiDocBundle)

The application is using a workflow of Controller -> Service -> Repository.

DTOs were created (RequestParameters) to map the request parameters. Since the parameters from the request come by 
default as strings, type hinting was omitted in some cases to allow the use of the DTO to validate the data through 
other ways.

The Service was introduced to decouple the Repository and entity manipulation from the Controller.

This is an initial architecture/version, but there are already some points that could be improved for the future:
 - Creating a DTO for the response to decouple the Entity from it, giving more freedom of choice regarding its format.
 - Refactor the repository to not extend anymore from ServiceEntityRepository, but to have the entity manager injected 
 on it, along with introducing an interface.
 - Using a real money/currency representation for the price
 - Normalizing the database by splitting the artist in its own table
 - Possibly extracting the validation from the controller to a service
 - Improving the way the annotations for OpenApi are defined by finding ways of reusing schemas from other files, like 
 the DTOs for example.
 - Add unit tests to cover some parts of the code.

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

When starting the application for the first time, run the following command:

`make up-build-dev`

It should build the containers, do a composer install and start the containers. 

This is a list of some additional useful commands, more can be found in the `Makefile` 
 - `up` for just starting the containers
 - `down` for applying compose down command
 - `php-sh` to access the PHP container
 - `tests` to run the tests
 - `logs` to print the docker logs