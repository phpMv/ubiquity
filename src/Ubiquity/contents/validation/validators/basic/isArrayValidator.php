<?php

namespace Ubiquity\contents\validation\validators\basic;

use Ubiquity\contents\validation\validators\Validator;
use Ubiquity\contents\validation\validators\basic\NotEmptyValidator;


class IsArrayValidator extends Validator{

    public function __construct(){
        $this->message("This value must be array");
    }

    public function validate( $value ){
        parent::validate($value);
        if($this->notEmpty && is_array($value)){
            return true;
        }
        return false;

    }
}