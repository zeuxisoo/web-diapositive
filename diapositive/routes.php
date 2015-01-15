<?php
$app->get('/',       'Diapositive\Controllers\HomeController:index')->name('index.index');
$app->map('/signup', 'Diapositive\Controllers\HomeController:signup')->name('index.signup')->via('GET', 'POST');
$app->map('/signin', 'Diapositive\Controllers\HomeController:signin')->name('index.signin')->via('GET', 'POST');
