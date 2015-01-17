all:
	@echo "make deps"

deps:
	@php composer.phar install

server:
	@php -S localhost:8080 -t public

redis:
	redis-server /usr/local/etc/redis.conf

worker:
	VVERBOSE=1 COUNT=1 QUEUE='*' REDIS_BACKEND=localhost:6379 REDIS_BACKEND_DB=0 APP_INCLUDE=./diapositive/jobs.php ./vendor/bin/resque
