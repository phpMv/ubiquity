<?php
namespace services;

class IService{
    public function __construct(){
        echo 'IService instanciation';
    }
    
    public function do($someThink=""){
        echo 'do '.$someThink;
    }
}

