<?php
namespace services;

class IAllService{
    public function __construct(){
        echo 'IAllService instanciation';
    }
    
    public function do($someThink=""){
        echo 'do '.$someThink ."in IAllService";
    }
}

