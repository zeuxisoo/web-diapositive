all:
	@echo "make deps"

deps:
	@php composer.phar install

server:
	@php -S localhost:8080 -t public
