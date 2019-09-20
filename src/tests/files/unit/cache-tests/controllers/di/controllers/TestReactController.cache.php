<?php
return array("allS"=>function ($controller){
			return new \services\IAllService ();
			},"inj"=>function ($ctrl){
				return new \services\IAllService ();
			});
