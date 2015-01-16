<?php
namespace Diapositive\Middlewares;

use Slim\Slim;

class Route {

    public static function requireLogin() {
        return function() {
            $app = Slim::getInstance();

            if (empty($_SESSION['user']) === true || empty($_SESSION['user']['id']) === true) {
                $app->flash('error', 'Please sign in first');
                $app->redirectTo('index.signin');
            }
        };
    }

}
