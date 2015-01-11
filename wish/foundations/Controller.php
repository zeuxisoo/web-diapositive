<?php
namespace Wish\Foundations;

use Slim\Slim;

class Controller {

    protected $app;

    public function __construct() {
        $this->app = Slim::getInstance();
    }

    public function render($template, $data = [], $status = null) {
        $this->app->render($template, $data, $status);
    }

}
