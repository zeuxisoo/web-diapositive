<?php
error_reporting(E_ERROR | E_PARSE);

require_once __DIR__.'/application.php';

$app = new Diapositive\Application();
$app->registerConfig();
$app->registerAutoLoad();
$app->registerDatabase();
$app->registerJobBackend();

require_once JOBS_ROOT.'/MakeVideoJob.php';
