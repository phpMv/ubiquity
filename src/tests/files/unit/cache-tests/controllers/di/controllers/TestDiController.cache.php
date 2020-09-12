<?php
return array("iService"=>function($controller){return new services\IService();},"inj"=>function ($ctrl){return new \services\IAllService($ctrl);},"allS"=>function ($controller){return new \services\IAllService($controller);});
