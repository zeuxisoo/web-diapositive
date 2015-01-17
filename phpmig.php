<?php
require_once __DIR__.'/diapositive/application.php';

use Phpmig\Adapter;
use Pimple\Container;

//
$app = new Diapositive\Application();
$app->registerConfig();
$app->registerAutoLoad();
$app->registerDatabase();

//
$container = new Container();
$container['db'] = function() {
    return ORM::get_db();
};
$container['phpmig.adapter'] = function() use($container) {
    return new Adapter\PDO\Sql($container['db'],'migrations');
};
$container['phpmig.migrations_path'] = __DIR__ . DIRECTORY_SEPARATOR . 'migrations';

return $container;
