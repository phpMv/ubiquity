<?php

namespace Ubiquity\controllers\admin\traits;

use Ajax\JsUtils;
use Ajax\semantic\html\elements\HtmlInput;
use Ubiquity\seo\UrlParser;
use Ubiquity\controllers\Startup;
use Ubiquity\utils\http\URequest;
use Ubiquity\utils\base\UFileSystem;
use Ajax\semantic\components\validation\Rule;
use Ubiquity\cache\CacheManager;
use Ubiquity\utils\http\USession;
use Ubiquity\controllers\seo\SeoController;
use Ubiquity\utils\base\UIntrospection;

/**
 *
 * @author jc
 * @property JsUtils $jquery
 */
trait SeoTrait{

	abstract public function _getAdminData();

	abstract public function _getAdminViewer();

	abstract public function _getAdminFiles();

	abstract public function loadView($viewName, $pData=NULL, $asString=false);

	abstract public function seo();

	abstract protected function showSimpleMessage($content, $type, $icon="info", $timeout=NULL, $staticName=null);

	abstract protected function _createController($controllerName, $variables=[], $ctrlTemplate='controller.tpl', $hasView=false);

	public function displaySiteMap(...$params) {
		$controllerClass=\implode("\\", $params);
		$controllerSeo=new $controllerClass();
		USession::set("seo-sitemap", $controllerSeo);
		$array=$controllerSeo->_getArrayUrls();
		$parser=new UrlParser();
		$parser->parseArray($array, true);
		$parser->parse();
		$urls=$parser->getUrls();
		$dt=$this->jquery->semantic()->dataTable("dtSiteMap", 'Ubiquity\seo\Url', $urls);
		$dt->setFields([ 'location','lastModified','changeFrequency','priority','delete' ]);
		$dt->setCaptions([ 'Location','Last Modified','Change Frequency','Priority','' ]);
		$dt->fieldAsInput("location");
		$dt->setValueFunction("lastModified", function ($v, $o, $i) {
			$d=date('Y-m-d\TH:i', $v);
			$input=new HtmlInput("date-" . $i, 'datetime-local', $d);
			$input->setName("lastModified[]");
			return $input;
		});
		$freq=UrlParser::$frequencies;
		$dt->fieldAsDropDown('changeFrequency', \array_combine($freq, $freq));
		$dt->setValueFunction("priority", function ($v, $o, $i) {
			$input=new HtmlInput("priority-" . $i, 'number', $v);
			$f=$input->getDataField();
			$f->setProperty('name', 'priority[]');
			$f->setProperty('max', '1')->setProperty('min', '0')->setProperty('step', '0.1');
			return $input;
		});
		$dt->onNewRow(function ($row, $instance) {
			if ($instance->getExisting()) {
				$row->addClass('positive');
			} else {
				$row->setProperty('style', 'display: none;')->addClass('toToggle');
			}
		});

		$dt->setHasCheckboxes(true);
		$dt->setCheckedCallback(function ($object) {
			return $object->getExisting();
		});
		$dt->asForm();
		$dt->setSubmitParams($this->_getAdminFiles()->getAdminBaseRoute() . "/saveUrls", "#seo-details", [ 'attr' => '' ]);
		$this->jquery->execOn("click", "#saveUrls", '$("#frm-dtSiteMap").form("submit");');

		$this->jquery->click('#displayAllRoutes', '$(".toToggle").toggle();$(this).toggleClass("active");');
		$this->jquery->compile($this->view);

		$this->loadView($this->_getAdminFiles()->getViewSeoDetails(), [ "controllerClass" => $controllerClass,"urlsFile" => $controllerSeo->_getUrlsFilename() ]);
	}

	public function generateRobots() {
		$frameworkDir=Startup::getFrameworkDir();
		$config=Startup::getConfig();
		$siteUrl=$config["siteUrl"];
		$content=[ ];
		if (URequest::isPost()) {
			$template=UFileSystem::load($frameworkDir . "/admin/templates/robots.tpl");
			$urls=URequest::post('selection', [ ]);
			foreach ( $urls as $url ) {
				$content[]=\str_replace("%url%", URequest::cleanUrl($siteUrl . $url), $template);
			}
			if (\sizeof($content) > 0) {
				$appDir=Startup::getApplicationDir() . './../';
				$content=\implode("\n", $content);
				UFileSystem::save($appDir . DS . 'robots.txt', $content);
				$msg=$this->showSimpleMessage("The file <b>robots.txt</b> has been generated in " . $appDir, "success", "info circle");
			} else {
				$msg=$this->showSimpleMessage("Can not generate <b>robots.txt</b> if no SEO controller is selected.", "warning", "warning circle");
			}
			echo $msg;
			echo $this->jquery->compile($this->view);
		}
	}

	public function _newSeoController() {
		$modal=$this->jquery->semantic()->htmlModal("modalNewSeo", "Creating a new Seo controller");
		$modal->setInverted();
		$frm=$this->jquery->semantic()->htmlForm("frmNewSeo");
		$fc=$frm->addField('controllerName')->addRule([ "checkController","Controller {value} already exists!" ]);
		$fc->labeled(Startup::getNS());
		$fields=$frm->addFields([ "urlsFile","sitemapTemplate" ], "Urls file & sitemap twig template");
		$fields->setFieldsPropertyValues("value", [ "urls","Seo/sitemap.xml.html" ]);
		$fields->getItem(0)->addRules([ "empty" ]);

		$frm->addCheckbox("ck-add-route", "Add route...");

		$frm->addContent("<div id='div-new-route' style='display: none;'>");
		$frm->addDivider();
		$frm->addInput("path", "", "text", "")->addRule([ "checkRoute","Route {value} already exists!" ]);
		$frm->addContent("</div>");

		$frm->setValidationParams([ "on" => "blur","inline" => true ]);
		$frm->setSubmitParams($this->_getAdminFiles()->getAdminBaseRoute() . "/createSeoController", "#main-content");
		$modal->setContent($frm);
		$modal->addAction("Validate");
		$this->jquery->click("#action-modalNewSeo-0", "$('#frmNewSeo').form('submit');", false, false);
		$modal->addAction("Close");
		$this->jquery->change('#controllerName', 'if($("#ck-add-route").is(":checked")){$("#path").val($(this).val());}');
		$this->jquery->exec("$('.dimmer.modals.page').html('');$('#modalNewSeo').modal('show');", true);
		$this->jquery->jsonOn("change", "#ck-add-route", $this->_getAdminFiles()->getAdminBaseRoute() . "/_addRouteWithNewAction", "post", [ "context" => "$('#frmNewSeo')","params" => "$('#frmNewSeo').serialize()","jsCondition" => "$('#ck-add-route').is(':checked')" ]);
		$this->jquery->exec(Rule::ajax($this->jquery, "checkRoute", $this->_getAdminFiles()->getAdminBaseRoute() . "/_checkRoute", "{}", "result=data.result;", "postForm", [ "form" => "frmNewSeo" ]), true);
		$this->jquery->exec(Rule::ajax($this->jquery, "checkController", $this->_getAdminFiles()->getAdminBaseRoute() . "/_checkController", "{}", "result=data.result;", "postForm", [ "form" => "frmNewSeo" ]), true);
		$this->jquery->change("#ck-add-route", '$("#div-new-route").toggle($(this).is(":checked"));if($(this).is(":checked")){$("#path").val($("#controllerName").val());}');
		echo $modal;
		echo $this->jquery->compile($this->view);
	}

	public function createSeoController($force=null) {
		if (URequest::isPost()) {
			$variables=[ ];
			$path=URequest::post("path");
			$variables["%path%"]=$path;
			if (isset($path)) {
				$variables["%route%"]='@route("' . $path . '")';
			}
			$variables["%urlsFile%"]=URequest::post("urlsFile", "urls");
			$variables["%sitemapTemplate%"]=URequest::post("sitemapTemplate", "Seo/sitemap.xml.html");

			$this->_createController($_POST["controllerName"], $variables, 'seoController.tpl');
		}
		$this->seo();
	}

	public function _checkController() {
		if (URequest::isPost()) {
			$result=[ ];
			$controllers=CacheManager::getControllers();
			$ctrlNS=Startup::getNS();
			header('Content-type: application/json');
			$controller=$ctrlNS . $_POST["controllerName"];
			$routes=CacheManager::getRoutes();
			$result["result"]=(\array_search($controller, $controllers) === false);
			echo json_encode($result);
		}
	}

	public function saveUrls() {
		$result=[ ];
		$selections=URequest::post("selection", [ ]);
		$locations=URequest::post("location", [ ]);
		$lastModified=URequest::post("lastModified", [ ]);
		$changeFrequency=URequest::post("changeFrequency", [ ]);
		$priority=URequest::post("priority", [ ]);
		foreach ( $selections as $index ) {
			$result[]=[ "location" => $locations[$index - 1],"lastModified" => \strtotime($lastModified[$index - 1]),"changeFrequency" => $changeFrequency[$index - 1],"priority" => $priority[$index - 1] ];
		}
		$seoController=USession::get("seo-sitemap");
		if (isset($seoController) && $seoController instanceof SeoController) {
			$seoController->_save($result);
			$r=new \ReflectionClass($seoController);
			$this->displaySiteMap($r->getNamespaceName(), $r->getShortName());
		}
	}
}
