<?php
namespace Diapositive\Foundations;

class Model {

    public static function factory($table) {
        return \Model::factory('Diapositive\\Models\\'.ucfirst($table));
    }

}
