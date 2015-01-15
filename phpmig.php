<?php
date_default_timezone_set("Asia/Hong_Kong");

define('WWW_ROOT',     __DIR__);
define('VENDOR_ROOT',  WWW_ROOT.'/vendor');
define('CONFIG_ROOT',  WWW_ROOT.'/config');
define('STORAGE_ROOT', WWW_ROOT.'/storage');

require VENDOR_ROOT.'/autoload.php';

use Phpmig\Adapter;
use Pimple\Container;

//
$config_default    = require_once CONFIG_ROOT.'/default.php';
$config_production = CONFIG_ROOT.'/production.php';

$config = $config_default;
if (file_exists($config_production) === true && is_file($config_production) === true) {
    $config_production = require_once $config_production;

    if (is_array($config_production) === true) {
        $config = array_merge($config_default, $config_production);
    }
}
unset($config_default, $config_production);

$database    = $config['database']['default'];
$connections = $config['database']['connections'];
if (strtolower($database) === "sqlite") {
    ORM::configure('sqlite:'.$connections[$database]['database']);
}else{
    ORM::configure(sprintf(
        '%s:host=%s;dbname=%s',
        $connections[$database]['driver'],
        $connections[$database]['host'],
        $connections[$database]['database']
    ));
    ORM::configure('username', $connections[$database]['username']);
    ORM::configure('password', $connections[$database]['password']);
}

if (strtolower($connections[$database]['driver']) === "mysql") {
    ORM::configure('driver_options', array(
        PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'
    ));
}
unset($database, $connections);

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
