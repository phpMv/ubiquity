<?php
namespace Ubiquity\db\export;

use Ubiquity\controllers\Startup;
use Ubiquity\cache\CacheManager;
use Ubiquity\orm\parser\ManyToManyParser;

class DbExport{
	protected $manyToManys=[];

	public function __construct(){

	}

	public function exports(){
		$result=[];
		$config=Startup::getConfig();
		$models=CacheManager::getModels($config,true);
		foreach ($models as $model){
			$tableExport=new TableExport($model);
			$result[]=$tableExport->exports($this);
		}
		foreach ($this->manyToManys as $target=>$ManyToManyParser){
			$ManyToManyParser->init();
			$sqlExport=new SqlExport();
			$fields=[$ManyToManyParser->getFkField(),$ManyToManyParser->getMyFkField()];
			$result[]=$sqlExport->exports($target,$fields);
		}
		return \implode("\n", $result);
	}

	public function addManyToMany($jointable,$memberTargetEntity){
		if(!isset($this->manyToManys[$jointable])){
			$this->manyToManys[$jointable]=new ManyToManyParser($memberTargetEntity["class"], $memberTargetEntity["member"]);
		}
	}
}
