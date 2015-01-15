<?php
namespace Diapositive\Foundations;

use Slim\Slim;

class Controller {

    protected $app;
    protected $request;

    public function __construct() {
        $this->app     = Slim::getInstance();
        $this->request = $this->app->request;
    }

    public function render($template, $data = [], $status = null) {
        $this->app->render($template, $data, $status);
    }

    public function flash($key, $value) {
        $this->app->flash($key, $value);
    }

    public function redirect($url, $status = 302) {
        $this->app->redirect($url, $status);
    }

    public function redirectTo($route, $params = [], $status = 302){
        $this->app->redirect($this->app->urlFor($route, $params), $status);
    }

    public function urlFor($name, $params = []) {
        $this->app->urlFor($name, $params);
    }

}
