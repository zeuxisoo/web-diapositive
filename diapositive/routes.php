<?php
use Diapositive\Middlewares\Route;

$app->get('/',        'Diapositive\Controllers\HomeController:index')->name('index.index');
$app->map('/signup',  'Diapositive\Controllers\HomeController:signup')->name('index.signup')->via('GET', 'POST');
$app->map('/signin',  'Diapositive\Controllers\HomeController:signin')->name('index.signin')->via('GET', 'POST');
$app->get('/signout', 'Diapositive\Controllers\HomeController:signout')->name('index.signout');

$app->group('/slideshow', Route::requireLogin(), function() use ($app) {
    $app->get('/index',          'Diapositive\Controllers\SlideShowController:index')->name('slideshow.index');
    $app->map('/create',         'Diapositive\Controllers\SlideShowController:create')->name('slideshow.create')->via('GET', 'POST');
    $app->post('/upload',        'Diapositive\Controllers\SlideShowController:upload')->name('slideshow.upload');
    $app->get('/download/:uuid', 'Diapositive\Controllers\SlideShowController:download')->name('slideshow.download');
    $app->get('/status',         'Diapositive\Controllers\SlideShowController:status')->name('slideshow.status');
});
