<?php
namespace Wish\Controllers;

use Wish\Foundations\Controller;

class HomeController extends Controller {

    public function index() {
        $this->render('home/index.html');
    }

}
