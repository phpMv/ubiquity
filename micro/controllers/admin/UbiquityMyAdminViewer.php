<?php

namespace micro\controllers\admin;

use Ajax\JsUtils;
use Ajax\semantic\widgets\dataform\DataForm;
use micro\orm\OrmUtils;
use Ajax\service\JArray;
use micro\orm\DAO;
use micro\orm\parser\Reflexion;
use Ajax\semantic\html\elements\HtmlHeader;
use Ajax\common\html\HtmlCollection;
use Ajax\common\html\BaseHtml;
use micro\cache\CacheManager;
use Ajax\semantic\html\elements\HtmlIcon;
use Ajax\semantic\html\base\constants\TextAlignment;
use micro\controllers\admin\popo\ControllerAction;
use Ajax\semantic\html\elements\HtmlLabel;
use Ajax\semantic\html\base\HtmlSemDoubleElement;
use Ajax\semantic\widgets\dataelement\DataElement;
use Ajax\semantic\html\elements\HtmlButton;
use Ajax\semantic\html\elements\html5\HtmlLink;
use Ajax\semantic\html\elements\HtmlButtonGroups;
use Ajax\semantic\widgets\datatable\DataTable;
use Ajax\service\JString;

/**
 * @author jc
 *
 */
class UbiquityMyAdminViewer {
	/**
	 * @var JsUtils
	 */
	private $jquery;

	/**
	 * @var UbiquityMyAdminBaseController
	 */
	private $controller;

	public function __construct(UbiquityMyAdminBaseController $controller){
		$this->jquery=$controller->jquery;
		$this->controller=$controller;
	}

	/**
	 * Returns the form for adding or modifying an object
	 * @param string $identifier
	 * @param object $instance the object to add or modify
	 * @return DataForm
	 */
	public function getForm($identifier,$instance){
		$form=$this->jquery->semantic()->dataForm($identifier, $instance);
		$className=\get_class($instance);
		$fields=$this->controller->_getAdminData()->getFormFieldNames($className);
		$form->setFields($fields);

		$fieldTypes=OrmUtils::getFieldTypes($className);
		foreach ($fieldTypes as $property=>$type){
			switch ($type){
			case "tinyint(1)":
				$form->fieldAsCheckbox($property);
				break;
			case "int": case "integer":
				$form->fieldAsInput($property,["inputType"=>"number"]);
				break;
			}
		}
		$this->relationMembersInForm($form, $instance, $className);
		$form->setCaptions($this->getFormCaptions($form->getInstanceViewer()->getVisibleProperties(),$className,$instance));
		$form->setSubmitParams($this->controller->_getAdminFiles()->getAdminBaseRoute()."/update", "#table-details");
		return $form;
	}

	/**
	 * Condition to determine if the edit or add form is modal for $model objects
	 * @param array $objects
	 * @param string $model
	 * @return boolean
	 */
	public function isModal($objects,$model){
		return \count($objects)>20;
	}

	/**
	 * Returns the captions for list fields in showTable action
	 * @param array $captions
	 * @param string $className
	 */
	public function getCaptions($captions,$className){
		return array_map("ucfirst", $captions);
	}

	/**
	 * Returns the captions for form fields
	 * @param array $captions
	 * @param string $className
	 */
	public function getFormCaptions($captions,$className,$instance){
		return array_map("ucfirst", $captions);
	}

	public function getFkHeaderElement($member,$className,$object){
		return new HtmlHeader("",4,$member,"content");
	}

	public function getFkHeaderList($member,$className,$list){
		return new HtmlHeader("",4,$member." (".\count($list).")","content");
	}

	/**
	 * @param string $member
	 * @param string $className
	 * @param object $object
	 * @return BaseHtml
	 */
	public function getFkElement($member,$className,$object){
		return $this->jquery->semantic()->htmlLabel("element-".$className.".".$member,$object."");
	}

	/**
	 * @param string $member
	 * @param string $className
	 * @param array|\Traversable $list
	 * @return HtmlCollection
	 */
	public function getFkList($member,$className,$list){
		$element=$this->jquery->semantic()->htmlList("list-".$className.".".$member);
		return $element->addClass("animated divided celled");
	}

	public function displayFkElementList($element,$member,$className,$object){

	}

	public function getMainMenuElements(){
		return ["models"=>["Models","sticky note","Used to perform CRUD operations on data."],
				"routes"=>["Routes","car","Displays defined routes with annotations"],
				"controllers"=>["Controllers","heartbeat","Displays controllers and actions"],
				"cache"=>["Cache","lightning","Annotations, models, router and controller cache"],
				"config"=>["Config","settings","Configuration variables"]
		];
	}

	public function getRoutesDataTable($routes){
		$dt=$this->jquery->semantic()->dataTable("dtRoutes", "micro\controllers\admin\popo\Route", $routes);
		$dt->setIdentifierFunction(function($i,$instance){return $instance->getId();});
		$dt->setFields(["path","methods","controller","action","parameters","cache","duration","name","expired"]);
		$dt->setCaptions(["Path","Methods","Controller","Action","Parameters","Cache","Duration","Name","Expired",""]);
		$dt->fieldAsLabel("path","car");
		$dt->fieldAsCheckbox("cache",["disabled"=>"disabled"]);
		$dt->setValueFunction("methods", function($v){return (\is_array($v))?"[".\implode(", ", $v)."]":$v;});
		$dt->setValueFunction("parameters", function($v){return (\is_array($v))?"[".\implode(", ", $v)."]":$v;});
		$dt->setValueFunction("expired", function($v,$instance,$index){
			$icon=null;$expired=null;
			if($instance->getCache()){
				if(\sizeof($instance->getParameters())===0 || $instance->getParameters()===null)
					$expired=CacheManager::isExpired($instance->getPath(),$instance->getDuration());
					if($expired===false){
						$icon=new HtmlIcon("", "toggle on");
					}elseif($expired===true){
						$icon=new HtmlIcon("", "toggle off");
					}else{
						$icon=new HtmlIcon("", "help");
					}
			}

			return $icon;
		});
		$dt->onRowClick('$("#filter-routes").val($(this).find(".ui.label").text());');
		$dt->onPreCompile(function($dTable){
			$dTable->setColAlignment(6, TextAlignment::RIGHT);
			$dTable->setColAlignment(8, TextAlignment::CENTER);
		});
		$this->addGetPostButtons($dt);
		$dt->setActiveRowSelector("warning");
		return $dt;
	}

	public function getControllersDataTable($controllers){
		$dt=$this->jquery->semantic()->dataTable("dtControllers", "micro\controllers\admin\popo\ControllerAction", $controllers);
		$dt->setFields(["controller","action","dValues"]);
		$dt->setIdentifierFunction(function($i,$instance){return \urlencode($instance->getController());});
		$dt->setCaptions(["Controller","Action [routes]","Default values",""]);
		$this->addGetPostButtons($dt);
		$dt->setValueFunction("controller", function($v,$instance,$index){
			$bt=new HtmlButton("bt-".\urlencode($v),$v);
			$bt->addClass("large _clickFirst");
			$bt->addIcon("heartbeat",true,true);
			$bt->setToggle();
			$bt->onClick("$(\"tr[data-ajax='".\urlencode($instance->getController())."'] td:not([rowspan])\").toggle(!$(this).hasClass('active'));");
			return $bt;
		});
		$dt->setValueFunction("action", function($v,$instance,$index){
			$params=$instance->getParameters();
			\array_walk($params, function(&$item){ $item= $item->name;});
			$params= " (".\implode(" , ", $params).")";
			$v=new HtmlSemDoubleElement("","span","","<b>".$v."</b>");
			$v->setProperty("style", "color: #3B83C0;");
			$v->addIcon("lightning");
			$v.=new HtmlSemDoubleElement("","span","",$params);
			$annots=$instance->getAnnots();
			foreach ($annots as $path=>$annotDetail){
				$lbl=new HtmlLabel("",$path,"car");
				$lbl->setProperty("data-ajax", \htmlspecialchars(($path)));
				$lbl->addClass("_route");
				$v.="&nbsp;".$lbl;
			}
			return $v;
		});
		$dt->onPreCompile(function($dt){
			$dt->getHtmlComponent()->mergeIdentiqualValues(0);
		});
		$dt->setEdition(true);
		$dt->addClass("compact");
		return $dt;
	}

	protected function addGetPostButtons(DataTable $dt){
		$dt->addFieldButtons(["GET","POST"],true,function(HtmlButtonGroups $bts,$instance,$index){
			$path=$instance->getPath();
			$bts->setIdentifier("bts-".$instance->getId()."-".$index);
			$bts->getItem(0)->addClass("_get")->setProperty("data-url",$path);
			$bts->getItem(1)->addClass("_post")->setProperty("data-url",$path);
			$item=$bts->addDropdown(["Post with parameters..."])->getItem(0);
			$item->addClass("_postWithParams")->setProperty("data-url",$path);
		});
	}

	public function getCacheDataTable($cacheFiles){
		$dt=$this->jquery->semantic()->dataTable("dtCacheFiles", "micro\controllers\admin\popo\CacheFile", $cacheFiles);
		$dt->setFields(["type","name","timestamp","size"]);
		$dt->setCaptions(["Type","Name","Timestamp","Size",""]);
		$dt->setValueFunction("type", function($v,$instance,$index){
			$item=$this->jquery->semantic()->htmlDropdown("dd-type-".$v,$v);
			$item->addItems(["Delete all","(Re-)Init cache"]);
			$item->setPropertyValues("data-ajax", $v);
			$item->getItem(0)->addClass("_delete-all");
			if($instance->getFile()==="")
				$item->getItem(0)->setDisabled();
			$item->getItem(1)->addClass("_init");
			if($instance->getType()!=="Models" && $instance->getType()!=="Controllers")
				$item->getItem(1)->setDisabled();
			$item->asButton()->addIcon("folder",true,true);
			return $item;
		});
		$dt->addDeleteButton(true,[],function($o,$instance){if($instance->getFile()=="")$o->setDisabled();});
		$dt->setIdentifierFunction("getFile");
		$dt->setValueFunction("timestamp", function($v){
			if($v!=="")
				return date(DATE_RFC2822,$v);
		});
		$dt->setValueFunction("size", function($v){
			if($v!=="")
			return self::formatBytes($v);
		});
		$dt->setValueFunction("name", function($name,$instance,$i){
			if(JString::isNotNull($name)){
				$link=new HtmlLink("lnl-".$i);
				$link->setContent($name);
				$link->addIcon("edit");
				$link->addClass("_lnk");
				$link->setProperty("data-type", $instance->getType());
				$link->setProperty("data-ajax", $instance->getFile());
				return $link;
			}
		});
		$dt->onPreCompile(function($dt){
			$dt->getHtmlComponent()->mergeIdentiqualValues(0);
		});
		$this->jquery->postOnClick("._lnk", $this->controller->_getAdminFiles()->getAdminBaseRoute()."/_showFileContent","{type:$(this).attr('data-type'),filename:$(this).attr('data-ajax')}","#modal");
		$this->jquery->postFormOnClick("._delete", $this->controller->_getAdminFiles()->getAdminBaseRoute()."/deleteCacheFile", "frmCache","#dtCacheFiles tbody",["jqueryDone"=>"replaceWith","params"=>"{toDelete:$(this).attr('data-ajax')}"]);
		$this->jquery->postFormOnClick("._delete-all", $this->controller->_getAdminFiles()->getAdminBaseRoute()."/deleteAllCacheFiles", "frmCache","#dtCacheFiles tbody",["jqueryDone"=>"replaceWith","params"=>"{type:$(this).attr('data-ajax')}"]);
		$this->jquery->postFormOnClick("._init", $this->controller->_getAdminFiles()->getAdminBaseRoute()."/initCacheType", "frmCache","#dtCacheFiles tbody",["jqueryDone"=>"replaceWith","params"=>"{type:$(this).attr('data-ajax')}"]);
		return $dt;
	}

	public function getModelsStructureDataTable($datas){
		$de=$this->jquery->semantic()->dataElement("dtStructure", $datas);
		$fields=\array_keys($datas);
		$de->setFields($fields);
		$de->setCaptions($fields);
		foreach ($fields as $key){
			$de->setValueFunction($key, function($value) use ($key){
				if($value instanceof  \stdClass){
					$value=(array) $value;
				}
				return \print_r($value,true);
			});
		}
		return $de;
	}

	public function getConfigDataElement($config){
		$de=$this->jquery->semantic()->dataElement("deConfig", $config);
		$fields=\array_keys($config);
		$de->setFields($fields);
		$de->setCaptions($fields);
		$de->setValueFunction("database", function($v,$instance,$index){
			$dbDe=new DataElement("",$v);
			$dbDe->setFields(["type","dbName","serverName","port","user","password","cache"]);
			$dbDe->setCaptions(["Type","dbName","serverName","port","user","password","cache"]);
			return $dbDe;
		});
		$de->setValueFunction("templateEngineOptions", function($v,$instance,$index){
			$teoDe=new DataElement("",$v);
			$teoDe->setFields(["cache"]);
			$teoDe->setCaptions(["cache"]);
			$teoDe->fieldAsCheckbox("cache",["class"=>"ui checkbox slider"]);
			return $teoDe;
		});
		$de->setValueFunction("mvcNS", function($v,$instance,$index){
			$mvcDe=new DataElement("",$v);
			$mvcDe->setFields(["models","controllers"]);
			$mvcDe->setCaptions(["Models","Controllers"]);
			return $mvcDe;
		});
		$de->setValueFunction("di", function($v,$instance,$index) use($config){
			$diDe=new DataElement("",$v);
			$keys=\array_keys($config["di"]);
			$diDe->setFields($keys);
			foreach ($keys as $key){
				$diDe->setValueFunction($key, function($value) use ($config,$key){
					$r =$config['di'][$key];
					return \nl2br(self::closure_dump($r));

				});
			}
			return $diDe;
		});
		$de->fieldAsCheckbox("test",["class"=>"ui checkbox slider"]);
		$de->fieldAsCheckbox("debug",["class"=>"ui checkbox slider"]);
		return $de;
	}

	private static function closure_dump(\Closure $c) {
		$str = 'function (';
		$r = new \ReflectionFunction($c);
		$params = array();
		foreach($r->getParameters() as $p) {
			$s = '';
			if($p->isArray()) {
				$s .= 'array ';
			} else if($p->getClass()) {
				$s .= $p->getClass()->name . ' ';
			}
			if($p->isPassedByReference()){
				$s .= '&';
			}
			$s .= '$' . $p->name;
			if($p->isOptional()) {
				$s .= ' = ' . \var_export($p->getDefaultValue(), TRUE);
			}
			$params []= $s;
		}
		$str .= \implode(', ', $params);
		$str .= '){' . PHP_EOL;
		$lines = file($r->getFileName());
		for($l = $r->getStartLine(); $l < $r->getEndLine(); $l++) {
			$str .= $lines[$l];
		}
		return $str;
	}

	private static function formatBytes($size, $precision = 2){
		$base = log($size, 1024);
		$suffixes = array('o', 'Ko', 'Mo', 'Go', 'To');
		return round(pow(1024, $base - floor($base)), $precision) .' '. $suffixes[floor($base)];
	}

	protected function relationMembersInForm($form,$instance,$className){
		$relations = OrmUtils::getFieldsInRelations($className);
		foreach ($relations as $member){
			if($this->controller->_getAdminData()->getUpdateManyToOneInForm() && OrmUtils::getAnnotationInfoMember($className, "#manyToOne",$member)!==false){
				$this->manyToOneFormField($form, $member, $className, $instance);
			}elseif($this->controller->_getAdminData()->getUpdateOneToManyInForm() && ($annot=OrmUtils::getAnnotationInfoMember($className, "#oneToMany",$member))!==false){
				$this->oneToManyFormField($form, $member, $instance,$annot);
			}elseif($this->controller->_getAdminData()->getUpdateManyToManyInForm() && ($annot=OrmUtils::getAnnotationInfoMember($className, "#manyToMany",$member))!==false){
				$this->manyToManyFormField($form, $member, $instance,$annot);
			}
		}
	}

	protected function manyToOneFormField(DataForm $form,$member,$className,$instance){
		$joinColumn=OrmUtils::getAnnotationInfoMember($className, "#joinColumn", $member);
		if($joinColumn){
			$fkObject=Reflexion::getMemberValue($instance, $member);
			$fkClass=$joinColumn["className"];
			if($fkObject===null){
				$fkObject=new $fkClass();
			}
			$fkId=OrmUtils::getFirstKey($fkClass);
			$fkIdGetter="get".\ucfirst($fkId);
			if(\method_exists($fkObject, "__toString") && \method_exists($fkObject, $fkIdGetter)){
				$fkField=$joinColumn["name"];
				$fkValue=OrmUtils::getFirstKeyValue($fkObject);
				if(!Reflexion::setMemberValue($instance, $fkField, $fkValue)){
					$instance->{$fkField}=OrmUtils::getFirstKeyValue($fkObject);
					$form->addField($fkField);
				}
				$form->fieldAsDropDown($fkField,JArray::modelArray(DAO::getAll($fkClass),$fkIdGetter,"__toString"));
				$form->setCaption($fkField, \ucfirst($member));
			}
		}
	}
	protected function oneToManyFormField(DataForm $form,$member,$instance,$annot){
		$newField=$member."Ids";
		$fkClass=$annot["className"];
		$fkId=OrmUtils::getFirstKey($fkClass);
		$fkIdGetter="get".\ucfirst($fkId);
		$fkInstances=DAO::getOneToMany($instance, $member);
		$form->addField($newField);
		$ids=\array_map(function($elm) use($fkIdGetter){return $elm->{$fkIdGetter}();},$fkInstances);
		$instance->{$newField}=\implode(",", $ids);
		$form->fieldAsDropDown($newField,JArray::modelArray(DAO::getAll($fkClass),$fkIdGetter,"__toString"),true);
		$form->setCaption($newField, \ucfirst($member));
	}

	protected function manyToManyFormField(DataForm $form,$member,$instance,$annot){
		$newField=$member."Ids";
		$fkClass=$annot["targetEntity"];
		$fkId=OrmUtils::getFirstKey($fkClass);
		$fkIdGetter="get".\ucfirst($fkId);
		$fkInstances=DAO::getManyToMany($instance, $member);
		$form->addField($newField);
		$ids=\array_map(function($elm) use($fkIdGetter){return $elm->{$fkIdGetter}();},$fkInstances);
		$instance->{$newField}=\implode(",", $ids);
		$form->fieldAsDropDown($newField,JArray::modelArray($this->controller->_getAdminData()->getManyToManyDatas($fkClass, $instance, $member),$fkIdGetter,"__toString"),true,["jsCallback"=>function($elm){$elm->getField()->asSearch();}]);
		$form->setCaption($newField, \ucfirst($member));
	}
}
