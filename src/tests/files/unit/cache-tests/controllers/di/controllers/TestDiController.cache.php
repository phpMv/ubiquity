<?php
return array("iService"=>function($controller){return new services\IService();},"inj"=>function ($ctrl=null){return new \services\IAllService($ctrl);},"allS"=>function ($controller=null){return new \services\IAllService($controller);});
