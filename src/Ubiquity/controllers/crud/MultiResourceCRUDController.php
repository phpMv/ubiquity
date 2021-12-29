<?php


namespace Ubiquity\controllers\crud;


use Ajax\php\ubiquity\JsUtils;
use Ajax\semantic\html\base\constants\Color;
use Ajax\semantic\html\base\constants\icons\Animals;
use Ajax\semantic\widgets\datatable\Pagination;
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
	
	private $displayedItems=[];
	
	private $_hasDropdown=false;
	
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
			$this->displayedItems[$resource]=$displayedItems=[
					'title'=>$myModel['title']??$this->getIndexDefaultTitle($resource),
					'desc'=>$myModel['desc']??$this->getIndexDefaultDesc($model),
					'resource'=>$resource,
					'icon'=>$myModel['icon']??$this->getIndexDefaultIcon($resource),
					'url'=>$myModel['url']??$this->getIndexDefaultUrl($resource),
					'meta'=>$myModel['meta']??$this->getIndexDefaultMeta($model),
					'actions'=>$myModel['actions']??null,
					'type'=>$type
			];
			$items[$resource]=$this->loadView($this->_getFiles()->getViewItemHome(),$displayedItems,true);
		}
		
		$data=['items'=>$items,'type'=>$mainType];
		if($this->hasNavigation()){
			$data['nav']=$this->nav($models);
		}
		$data['routeNamePrefix']=$this->getRouteNamePrefix();
		$this->onRenderView($data);
		$this->addIndexBehavior();
		$this->jquery->renderView($this->_getFiles()->getViewHome(),$data);
	}
	
	/**
	 * To override
	 * @param array $data
	 */
	protected function onRenderView(array &$data):void{
		
	}
	
	/**
	 * To override
	 * Return true for adding a navigation dropdown menu.
	 * @return bool
	 */
	protected function hasNavigation():bool{
		return true;
	}
	
	protected function getRouteNamePrefix():string {
		return '';
	}
	
	protected function nav(?array $models=null,string $btIcon='chevron right',string $btTitle='Navigate to...',bool $asString=true):?string{
		$this->_hasDropdown=true;
		$models??=$this->getIndexModels();
		$myModels=$this->getIndexModelsDetails();
		$items=[];
		foreach ($models as $model){
			$resource=\lcfirst(ClassUtils::getClassSimpleName($model));
			$items[]=$this->displayedItems[$resource]??['title'=>$myModels['title']??$this->getIndexDefaultTitle($resource),'icon'=>$myModels['icon']??$this->getIndexDefaultIcon($resource),'url'=>$myModels['url']??$this->getIndexDefaultUrl($resource)];
		}
		
		return $this->loadView($this->_getFiles()->getViewNav(),compact('items','btIcon','btTitle'),$asString);
	}
	
	protected function getIndexModels():array{
		return DAO::getModels('default');
	}
	
	protected function getIndexModelsDetails():array{
		return [];
	}
	
	protected function getIndexDefaultIcon(string $resource): string {
		return ' colored '.Animals::getRandomValue(true).' '.Color::getRandomValue(true);
	}
	
	protected function getIndexDefaultTitle(string $resource):string{
		return \ucfirst($resource);
	}
	
	protected function getIndexDefaultDesc(string $modelClass):string{
		return $modelClass;
	}
	
	protected function getIndexDefaultUrl(string $resource):string{
		return Router::path($this->getRouteNamePrefix().'crud.index',[$resource]);
	}
	
	protected function getIndexDefaultMeta(string $modelClass):?string{
		return null;
	}
	
	protected function addIndexBehavior():void{
		if($this->_hasDropdown){
			$this->jquery->execAtLast('$(".dropdown").dropdown();');
			$this->jquery->getOnClick('.item[data-href]','','.crud',['hasLoader'=>false,'preventDefault'=>false,'stopPropagation'=>false,'attr'=>'data-href']);
		}
		$this->jquery->getHref('a[href]',"",['historize'=>false,'hasLoader'=>true]);
	}
	
	protected function getIndexType():array {
		return ['four link cards','card'];
	}
	
	protected function getModelName(){
		return Startup::getNS('models') . \ucfirst($this->resource);
	}
	
	public abstract function _getBaseRoute():string;
	
	public function showDetail($ids) {
		$this->detailClick('showModelClick','.crud',[
				"attr" => "data-ajax",
				"hasLoader" => true
		]);
		parent::showDetail($ids);
		
	}
	public function showModelClick($modelAndId){
		$array = \explode("||", $modelAndId);
		if (\is_array($array)) {
			$m=$array[0];
			$this->model = $model = \str_replace('.', '\\',$m);
			$this->resource=\lcfirst(\substr($m, \strpos($m, ".") + 1));
			$id = $array[1];
			$totalCount = DAO::count($model, $this->_getAdminData()->_getInstancesFilter($model));
			$recordsPerPage = $this->_getModelViewer()->recordsPerPage($model, $totalCount);
			if (\is_numeric($recordsPerPage)) {
				if (isset($id)) {
					$rownum = DAO::getRownum($model, $id);
					$this->activePage = Pagination::getPageOfRow($rownum, $recordsPerPage);
				}
			}
			$this->jquery->execAtLast("$(\"tr[data-ajax='" . $id . "']\").click();");
			$this->index();
		}
	}
}
