<?php

namespace Ubiquity\contents\validation\validators\strings;

/**
 * Validates an email address
 * Usage @validator("ahsentekdemir.sh@gmail.com")
 *
 * @author Ahsen Tekdemir
 */

 class HashValidator extends RegexValidator{
    public function __construct(){
        $this->message = "{value} is not valid hash of  type {ref}";
        $this->ref(array(
            "/^[a-f0-9]{32}$/i", // MD5
            "/\b([a-f0-9]{40})\b/", //SHA1
            "\b[A-Fa-f0-9]{64}\b", //SHA256
            "\b[A-Fa-f0-9]{128}\b",  //SHA512
        ));
    }

    public function validate($value){
        parent::validate($value);
        $value = (string) $value;
        $flag = null;
        
        if($value != null and $value != false){
            if (isset(self::FLAGS[$this->ref])){
                $flag = self::FLAGS[$this-ref];
            }
            return filter_var($value, FILTER_VALIDATE_HASH, $flag);
        }
        return true;
    }

    public function getParameters(): array{
        return ["value", "ref"];
    }
 }
