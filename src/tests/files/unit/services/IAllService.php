<?php
namespace services;

use controllers\TestRestSimpleOrga;

class IAllService{
	public function __construct($controller=null){
		if(!($controller instanceof TestRestSimpleOrga)){
        	echo 'IAllService instanciation';
		}
    }

    public function do($someThink=""){
        echo 'do '.$someThink ."in IAllService";
    }
}

