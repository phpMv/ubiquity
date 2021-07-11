<?php


namespace ubiquity\controllers\crud;


use Ajax\php\ubiquity\JsUtils;
use Ajax\semantic\html\base\constants\Color;
use Ajax\semantic\html\base\constants\icons\Animals;
use Ubiquity\cache\CacheManager;
use Ubiquity\cache\ClassUtils;
use Ubiquity\controllers\Router;
use Ubiquity\controllers\Startup;
use Ubiquity\orm\DAO;

/**
 * Class MultiResourceCRUDController
 * @package controllers
 * @property JsUtils $jquery
 */
abstract class MultiResourceCRUDController extends \Ubiquity\controllers\crud\CRUDController {
	
	public string $resource='';
	
	public function initialize() {
		parent::initialize();
		$this->model = $this->getModelName();
	}

	public function home(){
		$models=$this->getIndexModels();
		$items=[];
		$myModels=$this->getIndexModelsDetails();
		list($mainType,$type)=$this->getIndexType();
		foreach ($models as $model){
			$resource=\lcfirst(ClassUtils::getClassSimpleName($model));
			$myModel=$myModels[$resource]??[];
			$items[$resource]=$this->loadView($this->_getFiles()->getViewItemHome(),[
				'title'=>$myModel['title']??$this->getIndexDefaultTitle($resource),
				'desc'=>$myModel['desc']??$this->getIndexDefaultDesc($model),
				'resource'=>$resource,
				'icon'=>$myModel['icon']??$this->getIndexDefaultIcon($resource),
				'url'=>$myModel['url']??$this->getIndexDefaultUrl($resource),
				'meta'=>$myModel['meta']??$this->getIndexDefaultMeta($model),
				'actions'=>$myModel['actions']??null,
				'type'=>$type
			],true);
		}
		$this->addIndexBehavior();
		$this->jquery->renderView($this->_getFiles()->getViewHome(),['items'=>$items,'type'=>$mainType]);
	}

	protected function getIndexModels():array{
		return CacheManager::getModels(Startup::$config,true);
	}

	protected function getIndexModelsDetails():array{
		return [];
	}

	protected function getIndexDefaultIcon(string $resource): string {
		return ' bordered colored '.Animals::getRandomValue(true).' '.Color::getRandomValue(true);
	}

	protected function getIndexDefaultTitle(string $resource):string{
		return \ucfirst($resource);
	}

	protected function getIndexDefaultDesc(string $modelClass):string{
		return $modelClass;
	}

	protected function getIndexDefaultUrl(string $resource):string{
		return Router::path('crud.index',[$resource]);
	}

	protected function getIndexDefaultMeta(string $modelClass):?string{
		return null;
	}

	protected function addIndexBehavior():void{
		$this->jquery->getHref('a[href]',"",['hasLoader'=>false,'historize'=>false]);
	}

	protected function getIndexType(){
		return ['four link cards','card'];
	}

	protected function getModelName(){
		return Startup::getNS('models') . \ucfirst($this->resource);
	}

	public abstract function _getBaseRoute():string;
}
