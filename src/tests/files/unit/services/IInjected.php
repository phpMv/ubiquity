<?php

namespace services;

class IInjected {
	public function __construct(){
		echo 'IInjected instanciation';
	}
	
	public function do($someThink=""){
		echo 'do '.$someThink;
	}
}

