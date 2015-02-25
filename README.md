# Web-Diapositive

The test application for how to create slideshow from images

## Installation

Install composer

	curl -sS https://getcomposer.org/installer | php
	
Install vendors

	make deps
	
Install database

	touch storage/default.sqlite
	php ./vendor/bin/phpmig migrate

## Run

Start the web server

	make server
	
Start the redis server

	make redis
	
Start the worker

	make worker
	
