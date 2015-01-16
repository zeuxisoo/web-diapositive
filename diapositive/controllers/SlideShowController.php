<?php
namespace Diapositive\Controllers;

use Diapositive\Foundations\Controller;

class SlideShowController extends Controller {

    public function create() {
        $this->render('slideshow/create.html');
    }

}
