<?php
ini_set('session.name', 's');
session_start();
header('Content-Type: text/html; charset=utf-8');
date_default_timezone_set("Asia/Hong_Kong");

define('WWW_ROOT',     dirname(__DIR__));
define('VENDOR_ROOT',  WWW_ROOT.'/vendor');
define('CONFIG_ROOT',  WWW_ROOT.'/config');
define('APP_ROOT',     WWW_ROOT.'/wish');
define('STORAGE_ROOT', WWW_ROOT.'/storage');

require_once VENDOR_ROOT.'/autoload.php';

use Slim\Slim;
use Slim\Views;
use Slim\Extras;

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

spl_autoload_register(function($_class) {
    $file_path = str_replace('\\', DIRECTORY_SEPARATOR, $_class);
    $path_info = pathinfo($file_path);
    $directory = strtolower($path_info['dirname']);

    $class_file = WWW_ROOT.'/'.$directory.DIRECTORY_SEPARATOR.$path_info['filename'].'.php';

    if (is_file($class_file) === true) {
        require_once $class_file;
    }
});

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

$app = new Slim([
    'debug'               => $config['debug'],
    'view'                => new Views\Twig(),
    'cookies.encrypt'     => true,
    'cookies.lifetime'    => $config['cookie']['life_time'],
    'cookies.path'        => $config['cookie']['path'],
    'cookies.domain'      => $config['cookie']['domain'],
    'cookies.secure'      => $config['cookie']['secure'],
    'cookies.httponly'    => $config['cookie']['httponly'],
    'cookies.secret_key'  => $config['cookie']['secret_key'],
    'cookies.cipher'      => MCRYPT_RIJNDAEL_256,
    'cookies.cipher_mode' => MCRYPT_MODE_CBC,
]);

$app->add(new Extras\Middleware\CsrfGuard());

$view = $app->view();
$view->twigTemplateDirs = [APP_ROOT.'/views'];
$view->parserOptions    = [
    'charset'          => 'utf-8',
    'cache'            => realpath(STORAGE_ROOT.'/views'),
    'auto_reload'      => true,
    'strict_variables' => false,
    'autoescape'       => true
];
$view->parserExtensions = [
    new Views\TwigExtension(),
];

require_once APP_ROOT.'/routes.php';

$app->run();
