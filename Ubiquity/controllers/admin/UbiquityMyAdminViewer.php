<?php

namespace Ubiquity\controllers\admin;

use Ajax\JsUtils;
use Ajax\semantic\widgets\dataform\DataForm;
use Ubiquity\orm\OrmUtils;
use Ajax\service\JArray;
use Ubiquity\orm\DAO;
use Ubiquity\orm\parser\Reflexion;
use Ajax\semantic\html\elements\HtmlHeader;
use Ajax\common\html\HtmlCollection;
use Ajax\common\html\BaseHtml;
use Ubiquity\cache\CacheManager;
use Ajax\semantic\html\elements\HtmlIcon;
use Ajax\semantic\html\base\constants\TextAlignment;
use Ajax\semantic\html\elements\HtmlLabel;
use Ajax\semantic\html\base\HtmlSemDoubleElement;
use Ajax\semantic\widgets\dataelement\DataElement;
use Ajax\semantic\html\elements\HtmlButton;
use Ajax\semantic\html\elements\html5\HtmlLink;
use Ajax\semantic\html\elements\HtmlButtonGroups;
use Ajax\semantic\widgets\datatable\DataTable;
use Ajax\service\JString;
use Ajax\semantic\html\elements\HtmlList;
use Ubiquity\controllers\admin\popo\Route;
use Ubiquity\utils\base\UString;
use Ajax\semantic\html\elements\HtmlIconGroups;
use Ajax\semantic\html\collections\HtmlMessage;
use Ubiquity\annotations\parser\DocParser;
use Ubiquity\cache\ClassUtils;
use Ajax\semantic\html\collections\form\HtmlFormCheckbox;
use Ajax\semantic\html\elements\HtmlLabelGroups;
use Ubiquity\utils\base\UIntrospection;
use Ajax\semantic\html\content\view\HtmlItem;
use Ajax\semantic\html\views\HtmlItems;

/**
 *
 * @author jc
 *
 */
class UbiquityMyAdminViewer {
	/**
	 *
	 * @var JsUtils
	 */
	private $jquery;

	/**
	 *
	 * @var UbiquityMyAdminBaseController
	 */
	private $controller;

	public function __construct(UbiquityMyAdminBaseController $controller) {
		$this->jquery=$controller->jquery;
		$this->controller=$controller;
	}

	/**
	 * Returns the form for adding or modifying an object
	 * @param string $identifier
	 * @param object $instance the object to add or modify
	 * @return DataForm
	 */
	public function getForm($identifier, $instance) {
		$type=($instance->_new) ? "new" : "edit";
		$messageInfos=[ "new" => [ "icon" => HtmlIconGroups::corner("table", "plus", "big"),"message" => "New object creation" ],"edit" => [ "icon" => HtmlIconGroups::corner("table", "edit", "big"),"message" => "Editing an existing object" ] ];
		$message=$messageInfos[$type];
		$form=$this->jquery->semantic()->dataForm($identifier, $instance);
		$className=\get_class($instance);
		$fields=$this->controller->_getAdminData()->getFormFieldNames($className);
		$form->setFields($fields);
		$form->insertField(0, "_message");
		$form->fieldAsMessage("_message", [ "icon" => $message["icon"] ]);
		$instance->_message=$className;
		$fieldTypes=OrmUtils::getFieldTypes($className);
		foreach ( $fieldTypes as $property => $type ) {
			switch($type) {
				case "tinyint(1)":
					$form->fieldAsCheckbox($property);
					break;
				case "int":
				case "integer":
					$form->fieldAsInput($property, [ "inputType" => "number" ]);
					break;
				case "date":
					$form->fieldAsInput($property, [ "inputType" => "date" ]);
					break;
				case "datetime":
					$form->fieldAsInput($property, [ "inputType" => "datetime-local" ]);
					break;
			}
		}
		$this->relationMembersInForm($form, $instance, $className);
		$form->setCaptions($this->getFormCaptions($form->getInstanceViewer()->getVisibleProperties(), $className, $instance));
		$form->setCaption("_message", $message["message"]);
		$form->setSubmitParams($this->controller->_getAdminFiles()->getAdminBaseRoute() . "/update", "#frm-add-update");
		return $form;
	}

	/**
	 * Returns the dataTable responsible for displaying instances of the model
	 * @param array $instances objects to display
	 * @param string $model model class name (long name)
	 * @return DataTable
	 */
	public function getModelDataTable($instances, $model) {
		$adminRoute=$this->controller->_getAdminFiles()->getAdminBaseRoute();
		$semantic=$this->jquery->semantic();

		$modal=($this->isModal($instances, $model) ? "modal" : "no");
		$lv=$semantic->dataTable("lv", $model, $instances);
		$attributes=$this->controller->getFieldNames($model);

		$lv->setCaptions($this->getCaptions($attributes, $model));
		$lv->setFields($attributes);
		$lv->onPreCompile(function () use ($attributes, &$lv) {
			$lv->getHtmlComponent()->colRight(\count($attributes));
		});

		$lv->setIdentifierFunction($this->controller->getIdentifierFunction($model));
		$lv->getOnRow("click", $adminRoute . "/showDetail", "#table-details", [ "attr" => "data-ajax" ]);
		$lv->setUrls([ "delete" => $adminRoute . "/delete","edit" => $adminRoute . "/edit/" . $modal ]);
		$lv->setTargetSelector([ "delete" => "#table-messages","edit" => "#frm-add-update" ]);
		$lv->addClass("small very compact");
		$lv->addEditDeleteButtons(false, [ "ajaxTransition" => "random" ], function ($bt) {
			$bt->addClass("circular");
		}, function ($bt) {
			$bt->addClass("circular");
		});
		$lv->setActiveRowSelector("error");
		$this->jquery->getOnClick("#btAddNew", $adminRoute . "/newModel/" . $modal, "#frm-add-update");
		$this->jquery->click("_.edit", "console.log($(this).closest('.ui.button'));");
		return $lv;
	}

	/**
	 * Condition to determine if the edit or add form is modal for $model objects
	 * @param array $objects
	 * @param string $model
	 * @return boolean
	 */
	public function isModal($objects, $model) {
		return \count($objects) > 5;
	}

	/**
	 * Returns the captions for list fields in showTable action
	 * @param array $captions
	 * @param string $className
	 */
	public function getCaptions($captions, $className) {
		return array_map("ucfirst", $captions);
	}

	/**
	 * Returns the captions for form fields
	 * @param array $captions
	 * @param string $className
	 */
	public function getFormCaptions($captions, $className, $instance) {
		return array_map("ucfirst", $captions);
	}

	public function getFkHeaderElement($member, $className, $object) {
		return new HtmlHeader("", 4, $member, "content");
	}

	public function getFkHeaderList($member, $className, $list) {
		return new HtmlHeader("", 4, $member . " (" . \count($list) . ")", "content");
	}

	/**
	 *
	 * @param string $member
	 * @param string $className
	 * @param object $object
	 * @return BaseHtml
	 */
	public function getFkElement($member, $className, $object) {
		return $this->jquery->semantic()->htmlLabel("element-" . $className . "." . $member, $object . "");
	}

	/**
	 *
	 * @param string $member
	 * @param string $className
	 * @param array|\Traversable $list
	 * @return HtmlCollection
	 */
	public function getFkList($member, $className, $list) {
		$element=$this->jquery->semantic()->htmlList("list-" . $className . "." . $member);
		return $element->addClass("animated divided celled");
	}

	public function displayFkElementList($element, $member, $className, $object) {
	}

	public function getMainMenuElements() {
		return [ "models" => [ "Models","sticky note","Used to perform CRUD operations on data." ],"routes" => [ "Routes","car","Displays defined routes with annotations" ],"controllers" => [ "Controllers","heartbeat","Displays controllers and actions" ],"cache" => [ "Cache","lightning","Annotations, models, router and controller cache" ],"rest" => [ "Rest","server","Restfull web service" ],"config" => [ "Config","settings","Configuration variables" ],"seo" => [ "Seo","google","Search Engine Optimization" ],"logs" => [ "Logs","bug","Log files" ] ];
	}

	public function getRoutesDataTable($routes, $dtName="dtRoutes") {
		$errors=[ ];
		$messages="";
		foreach ( $routes as $route ) {
			$errors=\array_merge($errors, $route->getMessages());
		}
		if (\sizeof($errors) > 0) {
			$messages=$this->controller->showSimpleMessage($errors, "error", "warning");
		}
		$dt=$this->jquery->semantic()->dataTable($dtName, "Ubiquity\controllers\admin\popo\Route", $routes);
		$dt->setIdentifierFunction(function ($i, $instance) {
			return $instance->getId();
		});
		$dt->setFields([ "path","methods","controller","action","cache","expired","name" ]);
		$dt->setCaptions([ "Path","Methods","Controller","Action & parameters","Cache","Expired","Name","" ]);
		$dt->fieldAsLabel("path", "car");
		$this->_dtCache($dt);
		$this->_dtMethods($dt);
		$this->_dtAction($dt);
		$this->_dtExpired($dt);
		$dt->onRowClick('$("#filter-routes").val($(this).find(".ui.label").text());');
		$dt->onPreCompile(function ($dTable) {
			$dTable->setColAlignment(7, TextAlignment::RIGHT);
			$dTable->setColAlignment(5, TextAlignment::CENTER);
		});
		$this->addGetPostButtons($dt);
		$dt->setActiveRowSelector("warning");
		$dt->wrap($messages);
		$dt->setEdition()->addClass("compact");
		return $dt;
	}

	public function getControllersDataTable($controllers) {
		$dt=$this->jquery->semantic()->dataTable("dtControllers", "Ubiquity\controllers\admin\popo\ControllerAction", $controllers);
		$dt->setFields([ "controller","action","dValues" ]);
		$dt->setIdentifierFunction(function ($i, $instance) {
			return \urlencode($instance->getController());
		});
		$dt->setCaptions([ "Controller","Action [routes]","Default values","" ]);
		$this->addGetPostButtons($dt);
		$dt->setValueFunction("controller", function ($v, $instance, $index) {
			$bts=new HtmlButtonGroups("bt-" . \urlencode($v), [ $v ]);
			$bts->addClass("basic");
			$bt=$bts->getItem(0);
			$bt->addClass("_clickFirst")->setIdentifier("bt-0-" . $v);
			$bt->addIcon("heartbeat", true, true);
			$bt->setToggle();
			$dd=$bts->addDropdown([ "Add new action in <b>{$v}</b>..." ]);
			$dd->setIcon("plus");
			$item=$dd->getItem(0);
			$item->addClass("_add-new-action")->setProperty("data-controller", $instance->getController());
			$bt->onClick("$(\"tr[data-ajax='" . \urlencode($instance->getController()) . "'] td:not([rowspan])\").toggle(!$(this).hasClass('active'));");
			return $bts;
		});
		$dt->setValueFunction("action", function ($v, $instance, $index) {
			$action=$v;
			$controller=ClassUtils::getClassSimpleName($instance->getController());
			$r=new \ReflectionMethod($instance->getController(), $action);
			$lines=file($r->getFileName());
			$params=$instance->getParameters();
			\array_walk($params, function (&$item) {
				$item=$item->name;
			});
			$params=" (" . \implode(" , ", $params) . ")";
			$v=new HtmlSemDoubleElement("", "span", "", "<b>" . $v . "</b>");
			$v->setProperty("style", "color: #3B83C0;");
			$v->addIcon("lightning");
			$v.=new HtmlSemDoubleElement("", "span", "", $params);
			$annots=$instance->getAnnots();
			foreach ( $annots as $path => $annotDetail ) {
				$lbl=new HtmlLabel("", $path, "car");
				$lbl->setProperty("data-ajax", \htmlspecialchars(($path)));
				$lbl->addClass("_route");
				$v.="&nbsp;" . $lbl;
			}
			$v=\array_merge([ $v,"<span class='_views-container'>" ], $this->getActionViews($instance->getController(), $controller, $action, $r, $lines));
			$v[]="</span>";
			return $v;
		});
		$dt->onPreCompile(function ($dt) {
			$dt->setColAlignment(3, TextAlignment::RIGHT);
			$dt->getHtmlComponent()->mergeIdentiqualValues(0);
		});
		$dt->setEdition(true);
		$dt->addClass("compact");
		return $dt;
	}

	public function getActionViews($controllerFullname, $controller, $action, \ReflectionMethod $r, $lines) {
		$result=[ ];
		$loadedViews=UIntrospection::getLoadedViews($r, $lines);
		foreach ( $loadedViews as $view ) {
			if (\file_exists(ROOT . DS . "views" . DS . $view)) {
				$lbl=new HtmlLabel("lbl-view-" . $controller . $action . $view, $view, "browser", "span");
				$lbl->addClass("violet");
				$lbl->addPopupHtml("<i class='icon info circle green'></i>&nbsp;<b>" . $view . "</b> is ok.");
			} else {
				$lbl=new HtmlLabel("lbl-view-" . $controller . $action . $view, $view, "warning", "span");
				$lbl->addClass("orange");
				$lbl->addPopupHtml("<i class='icon warning circle'></i>&nbsp;<b>" . $view . "</b> file is missing.");
			}
			$result[]=$lbl;
		}
		$viewname=$controller . "/" . $action . ".html";
		if (!\file_exists(ROOT . DS . "views" . DS . $viewname)) {
			$bt=new HtmlButton("", "Create view " . $viewname);
			$bt->setProperty("data-action", $action);
			$bt->setProperty("data-controller", $controller);
			$bt->setProperty("data-controllerFullname", $controllerFullname);
			$bt->addClass("_create-view visibleover basic violet mini")->setProperty("style", "visibility: hidden;")->addIcon("plus");
			$result[]=$bt;
		} elseif (\array_search($viewname, $loadedViews) === false) {
			$lbl=new HtmlLabel("lbl-view-" . $controller . $action . $viewname, $viewname, "browser", "span");
			$lbl->addPopupHtml("<i class='icon warning circle'></i>&nbsp;<b>" . $viewname . "</b> exists but is never loaded in action <b>" . $action . "</b>.");
			$result[]=$lbl;
		}
		return $result;
	}

	protected function addGetPostButtons(DataTable $dt) {
		$dt->addFieldButtons([ "GET","POST" ], true, function (HtmlButtonGroups $bts, $instance, $index) {
			$path=$instance->getPath();
			$path=\str_replace("(.*?)", "", $path);
			$path=\str_replace("(index/)?", "", $path);
			$bts->setIdentifier("bts-" . $instance->getId() . "-" . $index);
			$bts->getItem(0)->addClass("_get")->setProperty("data-url", $path);
			$bts->getItem(1)->addClass("_post")->setProperty("data-url", $path);
			$item=$bts->addDropdown([ "Post with parameters..." ])->getItem(0);
			$item->addClass("_postWithParams")->setProperty("data-url", $path);
		});
	}

	public function getCacheDataTable($cacheFiles) {
		$dt=$this->jquery->semantic()->dataTable("dtCacheFiles", "Ubiquity\controllers\admin\popo\CacheFile", $cacheFiles);
		$dt->setFields([ "type","name","timestamp","size" ]);
		$dt->setCaptions([ "Type","Name","Timestamp","Size","" ]);
		$dt->setValueFunction("type", function ($v, $instance, $index) {
			$item=$this->jquery->semantic()->htmlDropdown("dd-type-" . $v, $v);
			$item->addItems([ "Delete all","(Re-)Init cache" ]);
			$item->setPropertyValues("data-ajax", $v);
			$item->getItem(0)->addClass("_delete-all");
			if ($instance->getFile() === "")
				$item->getItem(0)->setDisabled();
			$item->getItem(1)->addClass("_init");
			if ($instance->getType() !== "Models" && $instance->getType() !== "Controllers")
				$item->getItem(1)->setDisabled();
			$item->asButton()->addIcon("folder", true, true);
			return $item;
		});
		$dt->addDeleteButton(true, [ ], function ($o, $instance) {
			if ($instance->getFile() == "")
				$o->setDisabled();
			$type=$instance->getType();
			$o->setProperty("data-type", $type);
			$type=\strtolower($type);
			if ($type == 'models' || $type == 'controllers') {
				$o->setProperty("data-key", $instance->getName());
			} else {
				$o->setProperty("data-key", $instance->getFile());
			}
		});
		$dt->setIdentifierFunction("getFile");
		$dt->setValueFunction("timestamp", function ($v) {
			if ($v !== "")
				return date(DATE_RFC2822, $v);
		});
		$dt->setValueFunction("size", function ($v) {
			if ($v !== "")
				return self::formatBytes($v);
		});
		$dt->setValueFunction("name", function ($name, $instance, $i) {
			if (JString::isNotNull($name)) {
				$link=new HtmlLink("lnl-" . $i);
				$link->setContent($name);
				$link->addIcon("edit");
				$link->addClass("_lnk");
				$link->setProperty("data-type", $instance->getType());
				$link->setProperty("data-ajax", $instance->getFile());
				$link->setProperty("data-key", $instance->getName());
				return $link;
			}
		});
		$dt->onPreCompile(function ($dt) {
			$dt->getHtmlComponent()->mergeIdentiqualValues(0);
		});
		$this->jquery->postOnClick("._lnk", $this->controller->_getAdminFiles()->getAdminBaseRoute() . "/_showFileContent", "{key:$(this).attr('data-key'),type:$(this).attr('data-type'),filename:$(this).attr('data-ajax')}", "#modal", [ "hasLoader" => false ]);
		$this->jquery->postFormOnClick("._delete", $this->controller->_getAdminFiles()->getAdminBaseRoute() . "/deleteCacheFile", "frmCache", "#dtCacheFiles tbody", [ "jqueryDone" => "replaceWith","params" => "{type:$(this).attr('data-type'),toDelete:$(this).attr('data-key')}" ]);
		$this->jquery->postFormOnClick("._delete-all", $this->controller->_getAdminFiles()->getAdminBaseRoute() . "/deleteAllCacheFiles", "frmCache", "#dtCacheFiles tbody", [ "jqueryDone" => "replaceWith","params" => "{type:$(this).attr('data-ajax')}" ]);
		$this->jquery->postFormOnClick("._init", $this->controller->_getAdminFiles()->getAdminBaseRoute() . "/initCacheType", "frmCache", "#dtCacheFiles tbody", [ "jqueryDone" => "replaceWith","params" => "{type:$(this).attr('data-ajax')}" ]);
		return $dt;
	}

	public function getModelsStructureDataTable($datas) {
		$de=$this->jquery->semantic()->dataElement("dtStructure", $datas);
		$fields=\array_keys($datas);
		$de->setFields($fields);
		$de->setCaptions($fields);
		foreach ( $fields as $key ) {
			$de->setValueFunction($key, function ($value) {
				if ($value instanceof \stdClass) {
					$value=( array ) $value;
				}
				return \print_r($value, true);
			});
		}
		return $de;
	}

	public function getRestRoutesTab($datas) {
		$tabs=$this->jquery->semantic()->htmlTab("tabsRest");

		foreach ( $datas as $controller => $restAttributes ) {
			$doc="";
			$list=new HtmlList("attributes", [ [ "heartbeat","Controller",$controller ],[ "car","Route",$restAttributes["restAttributes"]["route"] ] ]);
			$list->setHorizontal();
			if (\class_exists($controller)) {
				$parser=DocParser::docClassParser($controller);
				$desc=$parser->getDescriptionAsHtml();
				if (isset($desc)) {
					$doc=new HtmlMessage("msg-doc-controller-" . $controller, $desc);
					$doc->setIcon("help blue circle")->setDismissable()->addClass("transition hidden");
				}
			}
			$routes=Route::init($restAttributes["routes"]);
			$errors=[ ];
			foreach ( $routes as $route ) {
				$errors=\array_merge($errors, $route->getMessages());
			}
			$resource=$restAttributes["restAttributes"]["resource"];
			$tab=$tabs->addTab($resource, [ $doc,$list,$this->_getRestRoutesDataTable($routes, "dtRest", $resource, $restAttributes["restAttributes"]["authorizations"]) ]);
			if (\sizeof($errors) > 0) {
				$tab->menuTab->addLabel("error")->setColor("red")->addIcon("warning sign");
				$tab->addContent($this->controller->showSimpleMessage(\array_values($errors), "error", "warning"), true);
			}
			if ($doc !== "") {
				$tab->menuTab->addIcon("help circle blue")->onClick("$('#" . $doc->getIdentifier() . "').transition('horizontal flip');");
			}
		}
		return $tabs;
	}

	protected function _getRestRoutesDataTable($routes, $dtName, $resource, $authorizations) {
		$dt=$this->jquery->semantic()->dataTable($dtName, "Ubiquity\controllers\admin\popo\Route", $routes);
		$dt->setIdentifierFunction(function ($i, $instance) {
			return $instance->getPath();
		});
		$dt->setFields([ "path","methods","action","cache","expired" ]);
		$dt->setCaptions([ "Path","Methods","Action & Parameters","Cache","Exp?","" ]);
		$dt->fieldAsLabel("path", "car");
		$this->_dtCache($dt);
		$this->_dtMethods($dt);
		$dt->setValueFunction("action", function ($v, $instance) use ($authorizations) {
			$auth="";
			if (\array_search($v, $authorizations) !== false) {
				$auth=new HtmlIcon("lock-" . $instance->getController() . $v, "lock alternate");
				$auth->addPopup("Authorization", "This route require a valid access token");
			}
			$result=[ "<span style=\"color: #3B83C0;\">" . $v . "</span>" . $instance->getCompiledParams() . "<i class='ui icon help circle blue hidden transition _showMsgHelp' id='" . JString::cleanIdentifier("help-" . $instance->getAction() . $instance->getController()) . "' data-show='" . JString::cleanIdentifier("msg-help-" . $instance->getAction() . $instance->getController()) . "'></i>",$auth ];
			return $result;
		});
		$this->_dtExpired($dt);
		$dt->addFieldButton("Test", true, function ($bt, $instance) use ($resource) {
			$bt->addClass("toggle _toTest basic circular")->setProperty("data-resource", ClassUtils::cleanClassname($resource));
			$bt->setProperty("data-action", $instance->getAction())->setProperty("data-controller", \urlencode($instance->getController()));
		});
		$dt->onPreCompile(function ($dTable) {
			$dTable->setColAlignment(5, TextAlignment::RIGHT);
			$dTable->setColAlignment(4, TextAlignment::CENTER);
		});
		$dt->setEdition()->addClass("compact");
		return $dt;
	}

	protected function _dtMethods(DataTable $dt) {
		$dt->setValueFunction("methods", function ($v) {
			$result="";
			if (UString::isNotNull($v)) {
				if (!\is_array($v)) {
					$v=[ $v ];
				}
				$result=new HtmlLabelGroups("lbls-method", $v, [ "color" => "grey" ]);
			}
			return $result;
		});
	}

	protected function _dtCache(DataTable $dt) {
		$dt->setValueFunction("cache", function ($v, $instance) {
			$ck=new HtmlFormCheckbox("ck-" . $instance->getPath(), $instance->getDuration() . "");
			$ck->setChecked(UString::isBooleanTrue($v));
			$ck->setDisabled();
			return $ck;
		});
	}

	protected function _dtExpired(DataTable $dt) {
		$dt->setValueFunction("expired", function ($v, $instance, $index) {
			$icon=null;
			$expired=null;
			if ($instance->getCache()) {
				if (\sizeof($instance->getParameters()) === 0 || $instance->getParameters() === null)
					$expired=CacheManager::isExpired($instance->getPath(), $instance->getDuration());
				if ($expired === false) {
					$icon="hourglass full";
				} elseif ($expired === true) {
					$icon="hourglass empty orange";
				} else {
					$icon="help";
				}
			}
			return new HtmlIcon("", $icon);
		});
	}

	protected function _dtAction(DataTable $dt) {
		$dt->setValueFunction("action", function ($v, $instance) {
			$result="<span style=\"font-weight: bold;color: #3B83C0;\">" . $v . "</span>";
			$result.=$instance->getCompiledParams();
			if (!\method_exists($instance->getController(), $v)) {
				$errorLbl=new HtmlIcon("error-" . $v, "warning sign red");
				$errorLbl->addPopup("", "Missing method!");
				return [ $result,$errorLbl ];
			}
			return $result;
		});
	}

	public function getConfigDataElement($config) {
		$de=$this->jquery->semantic()->dataElement("deConfig", $config);
		$fields=\array_keys($config);
		$de->setFields($fields);
		$de->setCaptions($fields);
		$de->setValueFunction("database", function ($v, $instance, $index) {
			$dbDe=new DataElement("", $v);
			$dbDe->setFields([ "type","dbName","serverName","port","user","password","cache" ]);
			$dbDe->setCaptions([ "Type","dbName","serverName","port","user","password","cache" ]);
			return $dbDe;
		});
		$de->setValueFunction("templateEngineOptions", function ($v, $instance, $index) {
			$teoDe=new DataElement("", $v);
			$teoDe->setFields([ "cache" ]);
			$teoDe->setCaptions([ "cache" ]);
			$teoDe->fieldAsCheckbox("cache", [ "class" => "ui checkbox slider" ]);
			return $teoDe;
		});
		$de->setValueFunction("mvcNS", function ($v, $instance, $index) {
			$mvcDe=new DataElement("", $v);
			$mvcDe->setFields([ "models","controllers","rest" ]);
			$mvcDe->setCaptions([ "Models","Controllers","Rest" ]);
			return $mvcDe;
		});
		$de->setValueFunction("di", function ($v, $instance, $index) use ($config) {
			$diDe=new DataElement("", $v);
			$keys=\array_keys($config["di"]);
			$diDe->setFields($keys);
			foreach ( $keys as $key ) {
				$diDe->setValueFunction($key, function ($value) use ($config, $key) {
					$r=$config['di'][$key];
					if (\is_callable($r))
						return \nl2br(\htmlentities(UIntrospection::closure_dump($r)));
					return $value;
				});
			}
			return $diDe;
		});
		$de->setValueFunction("isRest", function ($v) use ($config) {
			$r=$config["isRest"];
			if (\is_callable($r))
				return \nl2br(\htmlentities(UIntrospection::closure_dump($r)));
			return $v;
		});
		$de->fieldAsCheckbox("test", [ "class" => "ui checkbox slider" ]);
		$de->fieldAsCheckbox("debug", [ "class" => "ui checkbox slider" ]);
		return $de;
	}

	private static function formatBytes($size, $precision=2) {
		$base=log($size, 1024);
		$suffixes=array ('o','Ko','Mo','Go','To' );
		return round(pow(1024, $base - floor($base)), $precision) . ' ' . $suffixes[floor($base)];
	}

	protected function relationMembersInForm($form, $instance, $className) {
		$relations=OrmUtils::getFieldsInRelations($className);
		foreach ( $relations as $member ) {
			if ($this->controller->_getAdminData()->getUpdateManyToOneInForm() && OrmUtils::getAnnotationInfoMember($className, "#manyToOne", $member) !== false) {
				$this->manyToOneFormField($form, $member, $className, $instance);
			} elseif ($this->controller->_getAdminData()->getUpdateOneToManyInForm() && ($annot=OrmUtils::getAnnotationInfoMember($className, "#oneToMany", $member)) !== false) {
				$this->oneToManyFormField($form, $member, $instance, $annot);
			} elseif ($this->controller->_getAdminData()->getUpdateManyToManyInForm() && ($annot=OrmUtils::getAnnotationInfoMember($className, "#manyToMany", $member)) !== false) {
				$this->manyToManyFormField($form, $member, $instance, $annot);
			}
		}
	}

	protected function manyToOneFormField(DataForm $form, $member, $className, $instance) {
		$joinColumn=OrmUtils::getAnnotationInfoMember($className, "#joinColumn", $member);
		if ($joinColumn) {
			$fkObject=Reflexion::getMemberValue($instance, $member);
			$fkClass=$joinColumn["className"];
			if ($fkObject === null) {
				$fkObject=new $fkClass();
			}
			$fkId=OrmUtils::getFirstKey($fkClass);
			$fkIdGetter="get" . \ucfirst($fkId);
			if (\method_exists($fkObject, "__toString") && \method_exists($fkObject, $fkIdGetter)) {
				$fkField=$joinColumn["name"];
				$fkValue=OrmUtils::getFirstKeyValue($fkObject);
				if (!Reflexion::setMemberValue($instance, $fkField, $fkValue)) {
					$instance->{$fkField}=OrmUtils::getFirstKeyValue($fkObject);
					$form->addField($fkField);
				}
				$form->fieldAsDropDown($fkField, JArray::modelArray(DAO::getAll($fkClass), $fkIdGetter, "__toString"));
				$form->setCaption($fkField, \ucfirst($member));
			}
		}
	}

	protected function oneToManyFormField(DataForm $form, $member, $instance, $annot) {
		$newField=$member . "Ids";
		$fkClass=$annot["className"];
		$fkId=OrmUtils::getFirstKey($fkClass);
		$fkIdGetter="get" . \ucfirst($fkId);
		$fkInstances=DAO::getOneToMany($instance, $member);
		$form->addField($newField);
		$ids=\array_map(function ($elm) use ($fkIdGetter) {
			return $elm->{$fkIdGetter}();
		}, $fkInstances);
		$instance->{$newField}=\implode(",", $ids);
		$form->fieldAsDropDown($newField, JArray::modelArray(DAO::getAll($fkClass), $fkIdGetter, "__toString"), true);
		$form->setCaption($newField, \ucfirst($member));
	}

	protected function manyToManyFormField(DataForm $form, $member, $instance, $annot) {
		$newField=$member . "Ids";
		$fkClass=$annot["targetEntity"];
		$fkId=OrmUtils::getFirstKey($fkClass);
		$fkIdGetter="get" . \ucfirst($fkId);
		$fkInstances=DAO::getManyToMany($instance, $member);
		$form->addField($newField);
		$ids=\array_map(function ($elm) use ($fkIdGetter) {
			return $elm->{$fkIdGetter}();
		}, $fkInstances);
		$instance->{$newField}=\implode(",", $ids);
		$form->fieldAsDropDown($newField, JArray::modelArray($this->controller->_getAdminData()->getManyToManyDatas($fkClass, $instance, $member), $fkIdGetter, "__toString"), true, [ "jsCallback" => function ($elm) {
			$elm->getField()->asSearch();
		} ]);
		$form->setCaption($newField, \ucfirst($member));
	}

	public function getMainIndexItems($identifier,$array):HtmlItems{
		$items=$this->jquery->semantic()->htmlItems($identifier);

		$items->fromDatabaseObjects($array, function ($e) {
			$item=new HtmlItem("");
			$item->addIcon($e[1] . " bordered circular")->setSize("big");
			$item->addItemHeaderContent($e[0], [ ], $e[2]);
			$item->setProperty("data-ajax", \strtolower($e[0]));
			return $item;
		});
			$items->getOnClick($this->controller->_getAdminFiles()->getAdminBaseRoute(), "#main-content", [ "attr" => "data-ajax" ]);
		return $items->addClass("divided relaxed link");
	}
}
