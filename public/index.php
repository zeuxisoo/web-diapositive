<?php
require_once dirname(__DIR__).'/diapositive/application.php';

$app = new Diapositive\Application();
$app->registerConfig();
$app->registerAutoLoad();
$app->registerDatabase();
$app->registerJobBackend();
$app->bootWebsite();
