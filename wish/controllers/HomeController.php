<?php
namespace Wish\Controllers;

use Wish\Bases\Controller;

class HomeController extends Controller {

    public function index() {
        $this->render('home/index.html');
    }

}
