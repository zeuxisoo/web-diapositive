<?php
namespace Diapositive\Helpers;

use Carbon\Carbon;

class Twig extends \Twig_Extension {

    public function getName() {
        return 'Diapositive';
    }

    public function getFunctions() {
        return [];
    }

    public function getFilters() {
        return [
            new \Twig_SimpleFilter('timeago', [$this, 'timeago']),
        ];
    }

    public function timeago($timestamp) {
        return Carbon::createFromTimeStamp($timestamp)->diffForHumans();
    }

}
