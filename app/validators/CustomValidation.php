<?php
namespace App\validators;

use \Illuminate\Validation\Validator as IlluminateValidator;

class CustomValidator extends IlluminateValidator {

public function validateAlphaSpaces($field, $value, $paramater) {

	if (preg_match("/^[\pL\pN\s_-]+$/u", $value) == true) {
            return true;        
        }
        return false;

	}


}

