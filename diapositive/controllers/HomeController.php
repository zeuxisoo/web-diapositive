<?php
namespace Diapositive\Controllers;

use Diapositive\Foundations\Controller;

class HomeController extends Controller {

    public function index() {
        $this->render('home/index.html');
    }

}
