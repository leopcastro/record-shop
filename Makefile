
DOCKER-COMPOSE-FILE-DEV="docker/docker-compose-dev.yml"

###########
### RUN ###
###########
up-build-dev: build-dev composer_install up

up:
	docker-compose -f $(DOCKER-COMPOSE-FILE-DEV) up -d

down:
	docker-compose -f $(DOCKER-COMPOSE-FILE-DEV) down

stop:
	docker-compose -f $(DOCKER-COMPOSE-FILE-DEV) stop

nginx-sh:
	docker-compose -f $(DOCKER-COMPOSE-FILE-DEV) exec rs-nginx sh

php-sh:
	docker-compose -f $(DOCKER-COMPOSE-FILE-DEV) exec rs-php sh


############
### LOGS ###
############
logs:
	docker-compose -f $(DOCKER-COMPOSE-FILE-DEV) logs


#############
### BUILD ###
#############
build-nginx:
	docker build -f docker/nginx/Dockerfile . \
	-t private/record-shop/nginx:latest

build-php-fpm-dev:
	docker build -f docker/php-fpm/Dockerfile . \
	--target DEV \
	-t private/record-shop/php-fpm-dev:latest

build-dev: build-nginx build-php-fpm-dev

composer_install:
	docker-compose -f $(DOCKER-COMPOSE-FILE-DEV) run --rm rs-php sh -c "composer install"


######################
### TESTS/ANALYSIS ###
######################
tests:
	docker-compose -f $(DOCKER-COMPOSE-FILE-DEV) run --rm rs-php sh -c "composer tests"

phpcs:
	docker-compose -f $(DOCKER-COMPOSE-FILE-DEV) run --rm rs-php sh -c "composer phpcs"

phpcbf:
	docker-compose -f $(DOCKER-COMPOSE-FILE-DEV) run --rm rs-php sh -c "composer phpcbf"