<?php

namespace Ubiquity\controllers\admin;

use Ajax\service\JString;
use Ajax\semantic\html\elements\HtmlHeader;
use Ajax\semantic\html\elements\HtmlButton;
use Ubiquity\orm\DAO;
use Ubiquity\orm\OrmUtils;
use Ubiquity\controllers\Startup;
use Ubiquity\utils\http\URequest;
use Ubiquity\cache\CacheManager;
use Ubiquity\controllers\admin\popo\Route;
use Ubiquity\controllers\Router;
use Ajax\semantic\html\collections\form\HtmlFormFields;
use Ubiquity\controllers\admin\popo\ControllerAction;
use Ubiquity\controllers\admin\traits\ModelsConfigTrait;
use Ubiquity\utils\base\UFileSystem;
use Ubiquity\utils\yuml\ClassToYuml;
use Ubiquity\utils\yuml\ClassesToYuml;
use Ajax\semantic\html\elements\HtmlList;
use Ajax\semantic\html\modules\HtmlDropdown;
use Ajax\semantic\html\collections\menus\HtmlMenu;
use Ajax\JsUtils;
use Ajax\semantic\html\base\constants\Direction;
use Ubiquity\controllers\admin\traits\RestTrait;
use Ubiquity\controllers\admin\traits\CacheTrait;
use Ubiquity\controllers\admin\traits\ControllersTrait;
use Ubiquity\controllers\admin\traits\ModelsTrait;
use Ubiquity\controllers\admin\traits\RoutesTrait;
use Ubiquity\utils\base\UString;
use Ubiquity\utils\UbiquityUtils;
use Ubiquity\controllers\admin\traits\DatabaseTrait;
use Ajax\semantic\html\collections\form\HtmlFormInput;
use Ajax\semantic\html\base\HtmlSemDoubleElement;
use Ubiquity\controllers\admin\popo\ControllerSeo;
use Ubiquity\controllers\admin\traits\SeoTrait;
use Ajax\semantic\html\collections\HtmlMessage;
use Ubiquity\controllers\admin\traits\GitTrait;
use Ubiquity\controllers\Controller;
use Ubiquity\controllers\admin\traits\ConfigTrait;
use Ubiquity\utils\http\UResponse;
use Ubiquity\utils\http\USession;
use Ubiquity\controllers\admin\viewers\ModelViewer;
use Ubiquity\controllers\admin\interfaces\HasModelViewerInterface;
use Ubiquity\controllers\semantic\MessagesTrait;
use Ubiquity\controllers\crud\CRUDDatas;
use Ubiquity\controllers\admin\traits\CreateControllersTrait;
use Ubiquity\cache\ClassUtils;

class UbiquityMyAdminBaseController extends Controller implements HasModelViewerInterface{
	
	use MessagesTrait,ModelsTrait,ModelsConfigTrait,RestTrait,CacheTrait,ConfigTrait,
	ControllersTrait,RoutesTrait,DatabaseTrait,SeoTrait,GitTrait,CreateControllersTrait;
	/**
	 *
	 * @var CRUDDatas
	 */
	private $adminData;

	/**
	 *
	 * @var UbiquityMyAdminViewer
	 */
	private $adminViewer;

	/**
	 *
	 * @var UbiquityMyAdminFiles
	 */
	private $adminFiles;

	/**
     * @var ModelViewer
     */
	private $adminModelViewer;
	
	private $globalMessage;

	public function initialize() {
		ob_start ( array (__class__,'_error_handler' ) );
		if (URequest::isAjax () === false) {
			$semantic = $this->jquery->semantic ();
			$mainMenuElements = $this->_getAdminViewer ()->getMainMenuElements ();
			$elements = [ "UbiquityMyAdmin" ];
			$dataAjax = [ "index" ];
			$hrefs=[$this->_getAdminFiles()->getAdminBaseRoute()."/index"];
			foreach ( $mainMenuElements as $elm => $values ) {
				$elements [] = $elm;
				$dataAjax [] = $values [0];
				$hrefs[]= $this->_getAdminFiles()->getAdminBaseRoute()."/".$values [0];
			}
			$mn = $semantic->htmlMenu ( "mainMenu", $elements );
			$mn->getItem ( 0 )->addClass ( "header" )->addIcon ( "home big link" );
			$mn->setPropertyValues ( "data-ajax", $dataAjax );
			$mn->setPropertyValues ( "href", $hrefs );
			$mn->setActiveItem ( 0 );
			$mn->setSecondary ();
			$mn->getOnClick ( "Admin", "#main-content", [ "attr" => "data-ajax","historize"=>true ] );
			$this->jquery->compile ( $this->view );
			$this->loadView ( $this->_getAdminFiles ()->getViewHeader () );
		}
	}

	public static function _error_handler($buffer) {
		$e = error_get_last ();
		if ($e) {
			if ($e ['file'] != 'xdebug://debug-eval' && !UResponse::isJSON()) {
				$staticName = "msg-" . rand ( 0, 50 );
				$message = new HtmlMessage ( $staticName );
				$message->addClass ( "error" );
				$message->addHeader ( "Error" );
				$message->addList ( [ "<b>Message :</b> " . $e ['message'],"<b>File :</b> " . $e ['file'],"<b>Line :</b> " . $e ['line'] ] );
				$message->setIcon ( "bug" );
				switch ($e ['type']) {
					case E_ERROR :
					case E_PARSE :
						return $message;
					default :
						return str_replace ( $e ['message'], "", $buffer ) . $message;
				}
			}else{
				return str_replace ( $e ['message'], "", $buffer );
			}
		}
		return $buffer;
	}

	public function finalize() {
		if (! URequest::isAjax ()) {
			$this->loadView ( "@framework/Admin/main/vFooter.html", [ "js" => $this->initializeJs () ] );
		}
		ob_end_flush ();
	}

	protected function initializeJs() {
		$js = 'var setAceEditor=function(elementId,readOnly,mode,maxLines){
			mode=mode || "sql";readOnly=readOnly || false;maxLines=maxLines || 100;
			var editor = ace.edit(elementId);
			editor.setTheme("ace/theme/solarized_dark");
			editor.getSession().setMode({path:"ace/mode/"+mode, inline:true});
			editor.setOptions({
				maxLines: maxLines,
				minLines: 2,
				showInvisibles: true,
				showGutter: !readOnly,
				showPrintMargin: false,
				readOnly: readOnly,
				showLineNumbers: !readOnly,
				highlightActiveLine: !readOnly,
				highlightGutterLine: !readOnly
				});
		};';
		return $this->jquery->inline ( $js );
	}

	public function index() {
		$semantic = $this->jquery->semantic ();
		$array = $this->_getAdminViewer ()->getMainMenuElements ();
		$this->_getAdminViewer ()->getMainIndexItems ( "part1", \array_slice ( $array, 0, 4 ) );
		$this->_getAdminViewer ()->getMainIndexItems ( "part2", \array_slice ( $array, 4, 4 ) );
		$this->jquery->compile ( $this->view );
		$this->loadView ( $this->_getAdminFiles ()->getViewIndex () );
	}

	public function models($hasHeader = true) {
		$semantic = $this->jquery->semantic ();
		$header = "";
		if ($hasHeader === true) {
			$header = $this->getHeader ( "models" );
			$stepper = $this->_getModelsStepper ();
		}
		if ($this->_isModelsCompleted () || $hasHeader !== true) {
			$config=Startup::getConfig();
			try {
				$models = CacheManager::getModels($config,true);
				$menu = $semantic->htmlMenu ( "menuDbs" );
				$menu->setVertical ()->setInverted ();
				foreach ( $models as $model ) {
					$count = DAO::count ( $model );
					$item = $menu->addItem ( ClassUtils::getClassSimpleName($model) );
					$item->addLabel ( $count );
					$tbl = OrmUtils::getTableName ( $model );
					$item->setProperty ( "data-ajax", $tbl );
					$item->setProperty ( "data-model", str_replace("\\", ".", $model));
				}
				$menu->getOnClick ( $this->_getAdminFiles ()->getAdminBaseRoute () . "/showModel", "#divTable", [ "attr" => "data-model" ,"historize"=>true] );
				$menu->onClick ( "$('.ui.label.left.pointing.teal').removeClass('left pointing teal');$(this).find('.ui.label').addClass('left pointing teal');" );
			} catch ( \Exception $e ) {
				throw $e;
				$this->showSimpleMessage ( "Models cache is not created!&nbsp;", "error","Exception", "warning circle", null, "errorMsg" );
			}
			$this->jquery->compile ( $this->view );
			$this->loadView ( $this->_getAdminFiles ()->getViewDataIndex () );
		} else {
			echo $header;
			echo $stepper;
			echo "<div id='models-main'>";
			$this->_loadModelStep ();
			echo "</div>";
			echo $this->jquery->compile ( $this->view );
		}
	}

	public function controllers() {
		$baseRoute=$this->_getAdminFiles()->getAdminBaseRoute();
		$this->getHeader ( "controllers" );
		$controllersNS = Startup::getNS ( 'controllers' );
		$controllersDir = ROOT . str_replace ( "\\", DS, $controllersNS );
		$this->showSimpleMessage ( "Controllers directory is <b>" . UFileSystem::cleanPathname ( $controllersDir ) . "</b>", "info",null, "info circle", null, "msgControllers" );
		$frm = $this->jquery->semantic ()->htmlForm ( "frmCtrl" );
		$frm->setValidationParams ( [ "on" => "blur","inline" => true ] );
		$fields=$frm->addFields();
		$input = $fields->addInput ( "name", null, "text", "", "Controller name" )->addRules ( [ [ "empty","Controller name must have a value" ],"regExp[/^[A-Za-z]\w*$/]" ] )->setWidth ( 8 );
		$input->labeledCheckbox ( Direction::LEFT, "View", "v", "slider" );
		$input->addAction ( "Create controller", true, "plus", true )->addClass ( "teal" )->asSubmit ();
		$frm->setSubmitParams ( $baseRoute. "/createController", "#main-content");
		$bt=$fields->addDropdown("crud-bt", ["frmAddCrudController"=>"CRUD controller","frmAddAuthController"=>"Auth controller"],"Create special controller");
		$bt->asButton();
		$bt->addIcon("plus");
		$this->jquery->getOnClick("#dropdown-crud-bt [data-value]",$baseRoute,"#frm",["attr"=>"data-value"]);
		$bt=$fields->addButton("filter-bt", "Filter controllers");
		$bt->getOnClick($baseRoute."/frmFilterControllers","#frm",["attr"=>""]);
		$bt->addIcon("filter");
		$this->_refreshControllers ();
		$this->jquery->compile ( $this->view );
		$this->loadView ( $this->_getAdminFiles ()->getViewControllersIndex () );
	}

	public function _refreshControllers($refresh = false) {
		$dt = $this->_getAdminViewer ()->getControllersDataTable ( ControllerAction::init () );
		$this->jquery->postOnClick ( "._route[data-ajax]", $this->_getAdminFiles ()->getAdminBaseRoute () . "/routes", "{filter:$(this).attr('data-ajax')}", "#main-content" );
		$this->jquery->postOnClick ( "._create-view", $this->_getAdminFiles ()->getAdminBaseRoute () . "/_createView", "{action:$(this).attr('data-action'),controller:$(this).attr('data-controller'),controllerFullname:$(this).attr('data-controllerFullname')}", '$(self).closest("._views-container")', [
				'hasLoader' => false ] );
		$this->jquery->execAtLast ( "$('#bt-0-controllersAdmin._clickFirst').click();" );
		$this->jquery->postOnClick ( "._add-new-action", $this->_getAdminFiles ()->getAdminBaseRoute () . "/_newActionFrm", "{controller:$(this).attr('data-controller')}", "#modal", [ "hasLoader" => false ] );
		$this->addNavigationTesting ();
		if ($refresh === "refresh") {
			echo $dt;
			echo $this->jquery->compile ( $this->view );
		}
	}

	public function routes() {
		$this->getHeader ( "routes" );
		$this->showSimpleMessage ( "Router cache entry is <b>" . CacheManager::$cache->getEntryKey ( "controllers\\routes.default" ) . "</b>", "info",null, "info circle", null, "msgRoutes" );
		$routes = CacheManager::getRoutes ();
		$this->_getAdminViewer ()->getRoutesDataTable ( Route::init ( $routes ) );
		$this->jquery->getOnClick ( "#bt-init-cache", $this->_getAdminFiles ()->getAdminBaseRoute () . "/initCacheRouter", "#divRoutes", [ "dataType" => "html","attr" => "" ] );
		$this->jquery->postOnClick ( "#bt-filter-routes", $this->_getAdminFiles ()->getAdminBaseRoute () . "/filterRoutes", "{filter:$('#filter-routes').val()}", "#divRoutes", [ "ajaxTransition" => "random" ] );
		if (isset ( $_POST ["filter"] ))
			$this->jquery->exec ( "$(\"tr:contains('" . $_POST ["filter"] . "')\").addClass('warning');", true );
		$this->addNavigationTesting ();
		$this->jquery->compile ( $this->view );
		$this->loadView ( $this->_getAdminFiles ()->getViewRoutesIndex (), [ "url" => Startup::getConfig () ["siteUrl"] ] );
	}

	protected function addNavigationTesting() {
		$this->jquery->postOnClick ( "._get", $this->_getAdminFiles ()->getAdminBaseRoute () . "/_runAction", "{method:'get',url:$(this).attr('data-url')}", "#modal", [ "hasLoader" => false ] );
		$this->jquery->postOnClick ( "._post", $this->_getAdminFiles ()->getAdminBaseRoute () . "/_runAction", "{method:'post',url:$(this).attr('data-url')}", "#modal", [ "hasLoader" => false ] );
		$this->jquery->postOnClick ( "._postWithParams", $this->_getAdminFiles ()->getAdminBaseRoute () . "/_runPostWithParams", "{url:$(this).attr('data-url')}", "#modal", [ "attr" => "","hasLoader" => false ] );
	}

	public function cache() {
		$this->getHeader ( "cache" );
		$this->showSimpleMessage ( CacheManager::$cache->getCacheInfo (), "info",null, "info circle", null, "msgCache" );

		$cacheFiles = CacheManager::$cache->getCacheFiles ( 'controllers' );
		$cacheFiles = \array_merge ( $cacheFiles, CacheManager::$cache->getCacheFiles ( 'models' ) );
		$form = $this->jquery->semantic ()->htmlForm ( "frmCache" );
		$radios = HtmlFormFields::checkeds ( "cacheTypes[]", [ "controllers" => "Controllers","models" => "Models","views" => "Views","queries" => "Queries","annotations" => "Annotations","seo" => "SEO" ], "Display cache types: ", [ "controllers","models" ] );
		$radios->postFormOnClick ( $this->_getAdminFiles ()->getAdminBaseRoute () . "/setCacheTypes", "frmCache", "#dtCacheFiles tbody", [ "jqueryDone" => "replaceWith" ] );
		$form->addField ( $radios )->setInline ();
		$this->_getAdminViewer ()->getCacheDataTable ( $cacheFiles );
		$this->jquery->compile ( $this->view );
		$this->loadView ( $this->_getAdminFiles ()->getViewCacheIndex () );
	}

	public function rest() {
		$this->getHeader ( "rest" );
		$this->showSimpleMessage ( "Router Rest cache entry is <b>" . CacheManager::$cache->getEntryKey ( "controllers\\routes.rest" ) . "</b>", "info","Rest service", "info circle", null, "msgRest" );
		$this->_refreshRest ();
		$this->jquery->getOnClick ( "#bt-init-rest-cache", $this->_getAdminFiles ()->getAdminBaseRoute () . "/initRestCache", "#divRest", [ "attr" => "","dataType" => "html" ] );
		$this->jquery->postOn ( "change", "#access-token", $this->_getAdminFiles ()->getAdminBaseRoute () . "/_saveToken", "{_token:$(this).val()}" );
		$token = "";
		if (isset ( $_SESSION ["_token"] )) {
			$token = $_SESSION ["_token"];
		}
		$this->jquery->getOnClick ( "#bt-new-resource", $this->_getAdminFiles ()->getAdminBaseRoute () . "/_frmNewResource", "#div-new-resource", [ "attr" => "" ] );
		$this->jquery->compile ( $this->view );
		$this->loadView ( $this->_getAdminFiles ()->getViewRestIndex (), [ "token" => $token ] );
	}

	public function config($hasHeader = true) {
		$config=Startup::getConfig();
		if ($hasHeader === true)
			$this->getHeader ( "config" );
		$this->_getAdminViewer ()->getConfigDataElement ( $config );
		$this->jquery->getOnClick("#edit-config-btn", $this->_getAdminFiles()->getAdminBaseRoute() . "/formConfig/ajax","#action-response",["jsCallback"=>'$("#config-div").hide();']);
		$this->jquery->compile ( $this->view );
		$this->loadView ( $this->_getAdminFiles ()->getViewConfigIndex () );
	}

	public function logs() {
		$this->getHeader ( "logs" );
		$this->jquery->compile ( $this->view );

		$this->loadView ( $this->_getAdminFiles ()->getViewLogsIndex () );
	}

	public function seo() {
		$this->getHeader ( "seo" );
		$this->_seo ();
		$this->jquery->compile ( $this->view );
		$this->loadView ( $this->_getAdminFiles ()->getViewSeoIndex () );
	}

	protected function _seo() {
		$ctrls = ControllerSeo::init ();
		$dtCtrl = $this->jquery->semantic ()->dataTable ( "seoCtrls", "Ubiquity\controllers\admin\popo\ControllerSeo", $ctrls );
		$dtCtrl->setFields ( [ 'name','urlsFile','siteMapTemplate','route','inRobots','see' ] );
		$dtCtrl->setIdentifierFunction ( 'getName' );
		$dtCtrl->setCaptions ( [ 'Controller name','Urls file','SiteMap template','Route','In robots?','' ] );
		$dtCtrl->fieldAsLabel ( 'route', 'car', [ 'jsCallback' => function ($lbl, $instance, $i, $index) {
			if ($instance->getRoute () == "") {
				$lbl->setProperty ( 'style', 'display:none;' );
			}
		} ] );
		$dtCtrl->fieldAsCheckbox ( 'inRobots', [ 'type' => 'toggle','disabled' => true ] );
		$dtCtrl->setValueFunction ( 'see', function ($value, $instance, $index) {
			if ($instance->urlExists ()) {
				$bt = new HtmlButton ( 'see-' . $index, '', '_see circular basic right floated' );
				$bt->setProperty ( "data-ajax", $instance->getName () );
				$bt->asIcon ( 'eye' );
				return $bt;
			}
		} );
		$dtCtrl->setValueFunction ( 'urlsFile', function ($value, $instance, $index) {
			if (! $instance->urlExists ()) {
				$elm = new HtmlSemDoubleElement ( 'urls-' . $index, 'span', '', $value );
				$elm->addIcon ( "warning circle red" );
				$elm->addPopup ( "Missing", $value . ' is missing!' );
				return $elm;
			}
			return $value;
		} );
		$dtCtrl->addDeleteButton ( false, [ ], function ($bt) {
			$bt->setProperty ( 'class', 'ui circular basic red right floated icon button _delete' );
		} );
		$dtCtrl->setTargetSelector ( [ "delete" => "#messages" ] );
		$dtCtrl->setUrls ( [ "delete" => $this->_getAdminFiles ()->getAdminBaseRoute () . "/deleteSeoController" ] );
		$dtCtrl->getOnRow ( 'click', $this->_getAdminFiles ()->getAdminBaseRoute () . '/displaySiteMap', '#seo-details', [ 'attr' => 'data-ajax','hasLoader' => false ] );
		$dtCtrl->setHasCheckboxes ( true );
		$dtCtrl->setSubmitParams ( $this->_getAdminFiles ()->getAdminBaseRoute () . '/generateRobots', "#messages", [ 'attr' => '','ajaxTransition' => 'random' ] );
		$dtCtrl->setActiveRowSelector ( 'error' );
		$this->jquery->getOnClick ( "._see", $this->_getAdminFiles ()->getAdminBaseRoute () . "/seeSeoUrl", "#messages", [ "attr" => "data-ajax" ] );
		$this->jquery->execOn ( 'click', '#generateRobots', '$("#frm-seoCtrls").form("submit");' );
		$this->jquery->getOnClick ( '#addNewSeo', $this->_getAdminFiles ()->getAdminBaseRoute () . '/_newSeoController', '#seo-details' );
		return $dtCtrl;
	}

	public function git($hasMessage = true) {
		$loader = '<div class="ui active inline centered indeterminate text loader">Waiting for git operation...</div>';
		$this->getHeader ( "git" );
		$gitRepo = $this->_getRepo ();
		$initializeBt = "";
		$pushPullBts = "";
		$gitIgnoreBt = "";
		$btRefresh = "";
		if (! $gitRepo->getInitialized ()) {
			$initializeBt = $this->jquery->semantic ()->htmlButton ( "initialize-bt", "Initialize repository", "orange" );
			$initializeBt->addIcon ( "magic" );
			$initializeBt->getOnClick ( $this->_getAdminFiles ()->getAdminBaseRoute () . "/gitInit", "#main-content", [ "attr" => "" ] );
			if ($hasMessage)
				$this->showSimpleMessage ( "<b>{$gitRepo->getName()}</b> respository is not initialized!", "warning",null, "warning circle", null, "init-message" );
		} else {
			if ($hasMessage) {
				$this->showSimpleMessage ( "<b>{$gitRepo->getName()}</b> repository is correctly initialized.", "info",null, "info circle", null, "init-message" );
			}
			$pushPullBts = $this->jquery->semantic ()->htmlButtonGroups ( "push-pull-bts", [ "3-Push","1-Pull" ] );
			$pushPullBts->addIcons ( [ "upload","download" ] );
			$pushPullBts->setPropertyValues ( "data-ajax", [ "gitPush","gitPull" ] );
			$pushPullBts->addPropertyValues ( "class", [ "blue","black" ] );
			$pushPullBts->getOnClick ( $this->_getAdminFiles ()->getAdminBaseRoute (), "#messages", [ "attr" => "data-ajax","ajaxLoader" => $loader ] );
			$pushPullBts->setPropertyValues ( "style", "width: 260px;" );
			$gitIgnoreBt = $this->jquery->semantic ()->htmlButton ( "gitIgnore-bt", ".gitignore" );
			$gitIgnoreBt->getOnClick ( $this->_getAdminFiles ()->getAdminBaseRoute () . "/gitIgnoreEdit", "#frm", [ "attr" => "" ] );
			$btRefresh = $this->jquery->semantic ()->htmlButton ( "refresh-bt", "Refresh files", "green" );
			$btRefresh->addIcon ( "sync alternate" );
			$btRefresh->getOnClick ( $this->_getAdminFiles ()->getAdminBaseRoute () . "/refreshFiles", "#dtGitFiles", [ "attr" => "","jqueryDone" => "replaceWith","hasLoader" => false ] );
		}

		$this->jquery->exec ( '$.fn.form.settings.rules.checkeds=function(value){var fields = $("[name=\'files-to-commit[]\']:checked");return fields.length>0;};', true );
		$files=$gitRepo->getFiles ();
		$this->_getAdminViewer ()->getGitFilesDataTable ( $files );
		$this->_getAdminViewer ()->getGitCommitsDataTable ( $gitRepo->getCommits () );
		
		$this->jquery->exec('$("#lbl-changed").toggle('.((sizeof($files)>0)?"true":"false").');',true);

		$this->jquery->getOnClick ( "#settings-btn", $this->_getAdminFiles ()->getAdminBaseRoute () . "/frmSettings", "#frm" );
		$this->jquery->exec ( '$("#commit-frm").form({"fields":{"summary":{"rules":[{"type":"empty"}]},"files-to-commit[]":{"rules":[{"type":"checkeds","prompt":"You must select at least 1 file!"}]}},"on":"blur","onSuccess":function(event,fields){' . $this->jquery->postFormDeferred ( $this->_getAdminFiles ()->getAdminBaseRoute () . "/commit", "commit-frm", "#messages", [
				"preventDefault" => true,"stopPropagation" => true,"ajaxLoader" => $loader ] ) . ';return false;}});', true );
		$this->jquery->exec ( '$("#git-tabs .item").tab();', true );
		$this->jquery->compile ( $this->view );
		$this->loadView ( $this->_getAdminFiles ()->getViewGitIndex (), [ "repo" => $gitRepo,"initializeBt" => $initializeBt,"gitIgnoreBt" => $gitIgnoreBt,"pushPullBts" => $pushPullBts,"btRefresh" => $btRefresh ] );
	}

	protected function getHeader($key) {
		$semantic = $this->jquery->semantic ();
		$header = $semantic->htmlHeader ( "header", 3 );
		$e = $this->_getAdminViewer ()->getMainMenuElements () [$key];
		$header->asTitle ( $e [0], $e [2] );
		$header->addIcon ( $e [1] );
		$header->setBlock ()->setInverted ();
		return $header;
	}

	public function _showDiagram() {
		if (URequest::isPost ()) {
			if (isset ( $_POST ["model"] )) {
				$model = $_POST ["model"];
				$model = \str_replace ( "|", "\\", $model );
				$modal = $this->jquery->semantic ()->htmlModal ( "diagram", "Class diagram : " . $model );
				$yuml = $this->_getClassToYuml ( $model, $_POST );
				$menu = $this->_diagramMenu ( "/_updateDiagram", "{model:'" . $_POST ["model"] . "',refresh:'true'}", "#diag-class" );
				$modal->setContent ( [ $menu,"<div id='diag-class' class='ui center aligned grid' style='margin:10px;'>",$this->_getYumlImage ( "plain", $yuml . "" ),"</div>" ] );
				$modal->addAction ( "Close" );
				$this->jquery->exec ( "$('#diagram').modal('show');", true );
				$modal->onHidden ( "$('#diagram').remove();" );
				echo $modal;
				echo $this->jquery->compile ( $this->view );
			}
		}
	}

	private function _getClassToYuml($model, $post) {
		if (isset ( $post ["properties"] )) {
			$props = \array_flip ( $post ["properties"] );
			$yuml = new ClassToYuml ( $model, isset ( $props ["displayProperties"] ), isset ( $props ["displayAssociations"] ), isset ( $props ["displayMethods"] ), isset ( $props ["displayMethodsParams"] ), isset ( $props ["displayPropertiesTypes"] ), isset ( $props ["displayAssociationClassProperties"] ) );
			if (isset ( $props ["displayAssociations"] )) {
				$yuml->init ( true, true );
			}
		} else {
			$yuml = new ClassToYuml ( $model, ! isset ( $_POST ["refresh"] ) );
			$yuml->init ( true, true );
		}
		return $yuml;
	}

	private function _getClassesToYuml($post) {
		if (isset ( $post ["properties"] )) {
			$props = \array_flip ( $post ["properties"] );
			$yuml = new ClassesToYuml ( isset ( $props ["displayProperties"] ), isset ( $props ["displayAssociations"] ), isset ( $props ["displayMethods"] ), isset ( $props ["displayMethodsParams"] ), isset ( $props ["displayPropertiesTypes"] ) );
		} else {
			$yuml = new ClassesToYuml ( ! isset ( $_POST ["refresh"] ), ! isset ( $_POST ["refresh"] ) );
		}
		return $yuml;
	}

	public function _updateDiagram() {
		if (URequest::isPost ()) {
			if (isset ( $_POST ["model"] )) {
				$model = $_POST ["model"];
				$model = \str_replace ( "|", "\\", $model );
				$type = $_POST ["type"];
				$size = $_POST ["size"];
				$yuml = $this->_getClassToYuml ( $model, $_POST );
				echo $this->_getYumlImage ( $type . $size, $yuml . "" );
				echo $this->jquery->compile ( $this->view );
			}
		}
	}

	/**
	 *
	 * @param string $url
	 * @param string $params
	 * @param string $responseElement
	 * @param string $type
	 * @return HtmlMenu
	 */
	private function _diagramMenu($url = "/_updateDiagram", $params = "{}", $responseElement = "#diag-class", $type = "plain", $size = ";scale:100") {
		$params = JsUtils::_implodeParams ( [ "$('#frmProperties').serialize()",$params ] );
		$menu = new HtmlMenu ( "menu-diagram" );
		$popup = $menu->addPopupAsItem ( "Display", "Parameters" );
		$list = new HtmlList ( "lst-checked" );
		$list->addCheckedList ( [ "displayPropertiesTypes" => "Types" ], [ "Properties","displayProperties" ], [ "displayPropertiesTypes" ], true, "properties[]" );
		$list->addCheckedList ( [ "displayMethodsParams" => "Parameters" ], [ "Methods","displayMethods" ], [ ], true, "properties[]" );
		$list->addCheckedList ( [ "displayAssociationClassProperties" => "Associated class members" ], [ "Associations","displayAssociations" ], [ "displayAssociations" ], true, "properties[]" );
		$btApply = new HtmlButton ( "bt-apply", "Apply", "green fluid" );
		$btApply->postOnClick ( $this->_getAdminFiles ()->getAdminBaseRoute () . $url, $params, $responseElement, [ "ajaxTransition" => "random","params" => $params,"attr" => "","jsCallback" => "$('#Parameters').popup('hide');" ] );
		$list->addItem ( $btApply );
		$popup->setContent ( $list );
		$ddScruffy = new HtmlDropdown ( "ddScruffy", $type, [ "nofunky" => "Boring","plain" => "Plain","scruffy" => "Scruffy" ], true );
		$ddScruffy->setValue ( "plain" )->asSelect ( "type" );
		$this->jquery->postOn ( "change", "#type", $this->_getAdminFiles ()->getAdminBaseRoute () . $url, $params, $responseElement, [ "ajaxTransition" => "random","attr" => "" ] );
		$menu->addItem ( $ddScruffy );
		$ddSize = new HtmlDropdown ( "ddSize", $size, [ ";scale:180" => "Huge",";scale:120" => "Big",";scale:100" => "Normal",";scale:80" => "Small",";scale:60" => "Tiny" ], true );
		$ddSize->asSelect ( "size" );
		$this->jquery->postOn ( "change", "#size", $this->_getAdminFiles ()->getAdminBaseRoute () . $url, $params, $responseElement, [ "ajaxTransition" => "random","attr" => "" ] );
		$menu->wrap ( "<form id='frmProperties' name='frmProperties'>", "</form>" );
		$menu->addItem ( $ddSize );
		return $menu;
	}

	public function _showAllClassesDiagram() {
		$yumlContent = new ClassesToYuml ();
		$menu = $this->_diagramMenu ( "/_updateAllClassesDiagram", "{refresh:'true'}", "#diag-class" );
		$this->jquery->exec ( '$("#modelsMessages-success").hide()', true );
		$menu->compile ( $this->jquery, $this->view );
		$form = $this->jquery->semantic ()->htmlForm ( "frm-yuml-code" );
		$textarea = $form->addTextarea ( "yuml-code", "Yuml code", \str_replace ( ",", ",\n", $yumlContent . "" ) );
		$textarea->getField ()->setProperty ( "rows", 20 );
		$diagram = $this->_getYumlImage ( "plain", $yumlContent );
		$this->jquery->execAtLast ( '$("#all-classes-diagram-tab .item").tab();' );
		$this->jquery->compile ( $this->view );
		$this->loadView ( $this->_getAdminFiles ()->getViewClassesDiagram (), [ "diagram" => $diagram ] );
	}

	public function _updateAllClassesDiagram() {
		if (URequest::isPost ()) {
			$type = $_POST ["type"];
			$size = $_POST ["size"];
			$yumlContent = $this->_getClassesToYuml ( $_POST );
			$this->jquery->exec ( '$("#yuml-code").html("' . \htmlentities ( $yumlContent . "" ) . '")', true );
			echo $this->_getYumlImage ( $type . $size, $yumlContent );
			echo $this->jquery->compile ();
		}
	}

	protected function _getYumlImage($sizeType, $yumlContent) {
		return "<img src='http://yuml.me/diagram/" . $sizeType . "/class/" . $yumlContent . "'>";
	}

	public function showDatabaseCreation() {
		$config = Startup::getConfig ();
		$models = $this->getModels ();
		$segment = $this->jquery->semantic ()->htmlSegment ( "menu" );
		$segment->setTagName ( "form" );
		$header = new HtmlHeader ( "", 5, "Database creation" );
		$header->addIcon ( "plus" );
		$segment->addContent ( $header );
		$input = new HtmlFormInput ( "dbName" );
		$input->setValue ( $config ["database"] ["dbName"] );
		$input->getField ()->setFluid ();
		$segment->addContent ( $input );
		$list = new HtmlList ( "lst-checked" );
		$list->addCheckedList ( [ "dbCreation" => "Creation","dbUse" => "Use" ], [ "Database","db" ], [ "use","creation" ], false, "dbProperties[]" );
		$list->addCheckedList ( $models, [ "Models [tables]","modTables" ], \array_keys ( $models ), false, "tables[]" );
		$list->addCheckedList ( [ "manyToOne" => "ManyToOne","oneToMany" => "oneToMany" ], [ "Associations","displayAssociations" ], [ "displayAssociations" ], false, "associations[]" );
		$btApply = new HtmlButton ( "bt-apply", "Create SQL script", "green fluid" );
		$btApply->postFormOnClick ( $this->_getAdminFiles ()->getAdminBaseRoute () . "/createSQLScript", "menu", "#div-create", [ "ajaxTransition" => "random","attr" => "" ] );
		$list->addItem ( $btApply );

		$segment->addContent ( $list );
		$this->jquery->compile ( $this->view );
		$this->loadView ( $this->_getAdminFiles ()->getViewDatabaseIndex () );
	}

	public function _runPostWithParams($method = "post", $type = "parameter", $origine = "routes") {
		if (URequest::isPost ()) {
			$model = null;
			$actualParams = [ ];
			$url = $_POST ["url"];
			if (isset ( $_POST ["method"] ))
				$method = $_POST ["method"];
			if (isset ( $_POST ["model"] )) {
				$model = $_POST ["model"];
			}

			if ($origine === "routes") {
				$responseElement = "#modal";
				$responseURL = "/_runAction";
				$jqueryDone = "html";
				$toUpdate = "";
			} else {
				$toUpdate = $_POST ["toUpdate"];
				$responseElement = "#" . $toUpdate;
				$responseURL = "/_saveRequestParams/" . $type;
				$jqueryDone = "replaceWith";
			}
			if (isset ( $_POST ["actualParams"] )) {
				$actualParams = $this->_getActualParamsAsArray ( $_POST ["actualParams"] );
			}
			$modal = $this->jquery->semantic ()->htmlModal ( "response-with-params", "Parameters for the " . \strtoupper ( $method ) . ":" . $url );
			$frm = $this->jquery->semantic ()->htmlForm ( "frmParams" );
			$frm->addMessage ( "msg", "Enter your " . $type . "s.", \ucfirst ( $method ) . " " . $type . "s", "info circle" );
			$index = 0;
			foreach ( $actualParams as $name => $value ) {
				$this->_addNameValueParamFields ( $frm, $type, $name, $value, $index ++ );
			}
			$this->_addNameValueParamFields ( $frm, $type, "", "", $index ++ );

			$fieldsButton = $frm->addFields ();
			$fieldsButton->addClass ( "_notToClone" );
			$fieldsButton->addButton ( "clone", "Add " . $type, "yellow" )->setTagName ( "div" );
			if (isset ( $model )) {
				$model = UbiquityUtils::getModelsName ( Startup::getConfig (), $model );
				$modelFields = OrmUtils::getSerializableFields ( $model );
				if (\sizeof ( $modelFields ) > 0) {
					$modelFields = \array_combine ( $modelFields, $modelFields );
					$ddModel = $fieldsButton->addDropdown ( "bt-addModel", $modelFields, "Add " . $type . "s from " . $model );
					$ddModel->asButton ();
					$this->jquery->click ( "#dropdown-bt-addModel .item", "
							var text=$(this).text();
							var count=0;
							var empty=null;
							$('#frmParams input[name=\'name[]\']').each(function(){
								if($(this).val()==text) count++;
								if($(this).val()=='') empty=this;
							});
							if(count<1){
								if(empty==null){
									$('#clone').click();
									var inputs=$('.fields:not(._notToClone)').last().find('input');
									inputs.first().val($(this).text());
								}else{
									$(empty).val($(this).text());
								}
							}
							" );
				}
			}
			if (isset ( $_COOKIE [$method] ) && \sizeof ( $_COOKIE [$method] ) > 0) {
				$dd = $fieldsButton->addDropdownButton ( "btMem", "Memorized " . $type . "s", $_COOKIE [$method] )->getDropdown ()->setPropertyValues ( "data-mem", \array_map ( "addslashes", $_COOKIE [$method] ) );
				$cookiesIndex = \array_keys ( $_COOKIE [$method] );
				$dd->each ( function ($i, $item) use ($cookiesIndex) {
					$bt = new HtmlButton ( "bt-" . $item->getIdentifier () );
					$bt->asIcon ( "remove" )->addClass ( "basic _deleteParam" );
					$bt->getOnClick ( $this->_getAdminFiles ()->getAdminBaseRoute () . "/_deleteCookie", null, [ "attr" => "data-value" ] );
					$bt->setProperty ( "data-value", $cookiesIndex [$i] );
					$bt->onClick ( "$(this).parents('.item').remove();" );
					$item->addContent ( $bt, true );
				} );
				$this->jquery->click ( "[data-mem]", "
						var objects=JSON.parse($(this).text());
						$.each(objects, function(name, value) {
							$('#clone').click();
							var inputs=$('.fields:not(._notToClone)').last().find('input');
							inputs.first().val(name);
							inputs.last().val(value);
						});
						$('.fields:not(._notToClone)').each(function(){
							var inputs=$(this).find('input');
							if(inputs.last().val()=='' && inputs.last().val()=='')
								if($('.fields').length>2)
									$(this).remove();
						});
						" );
			}
			$this->jquery->click ( "._deleteParameter", "
								if($('.fields').length>2)
									$(this).parents('.fields').remove();
					", true, true, true );
			$this->jquery->click ( "#clone", "
					var cp=$('.fields:not(._notToClone)').last().clone(true);
					var num = parseInt( cp.prop('id').match(/\d+/g), 10 ) +1;
					cp.find( '[id]' ).each( function() {
						var num = $(this).attr('id').replace( /\d+$/, function( strId ) { return parseInt( strId ) + 1; } );
						$(this).attr( 'id', num );
						$(this).val('');
					});
					cp.insertBefore($('#clone').closest('.fields'));" );
			$frm->setValidationParams ( [ "on" => "blur","inline" => true ] );
			$frm->setSubmitParams ( $this->_getAdminFiles ()->getAdminBaseRoute () . $responseURL, $responseElement, [ "jqueryDone" => $jqueryDone,"params" => "{toUpdate:'" . $toUpdate . "',method:'" . \strtoupper ( $method ) . "',url:'" . $url . "'}" ] );
			$modal->setContent ( $frm );
			$modal->addAction ( "Validate" );
			$this->jquery->click ( "#action-response-with-params-0", "$('#frmParams').form('submit');", false, false, false );

			$modal->addAction ( "Close" );
			$this->jquery->exec ( "$('.dimmer.modals.page').html('');$('#response-with-params').modal('show');", true );
			echo $modal->compile ( $this->jquery, $this->view );
			echo $this->jquery->compile ( $this->view );
		}
	}

	protected function _getActualParamsAsArray($urlEncodedParams) {
		$result = [ ];
		$params = [ ];
		\parse_str ( urldecode ( $urlEncodedParams ), $params );
		if (isset ( $params ["name"] )) {
			$names = $params ["name"];
			$values = $params ["value"];
			$count = \sizeof ( $names );
			for($i = 0; $i < $count; $i ++) {
				$name = $names [$i];
				if (UString::isNotNull ( $name )) {
					if (isset ( $values [$i] ))
						$result [$name] = $values [$i];
				}
			}
		}
		return $result;
	}

	protected function _addNameValueParamFields($frm, $type, $name, $value, $index) {
		$fields = $frm->addFields ();
		$fields->addInput ( "name[]", \ucfirst ( $type ) . " name" )->getDataField ()->setIdentifier ( "name-" . $index )->setProperty ( "value", $name );
		$input = $fields->addInput ( "value[]", \ucfirst ( $type ) . " value" );
		$input->getDataField ()->setIdentifier ( "value-" . $index )->setProperty ( "value", $value );
		$input->addAction ( "", true, "remove" )->addClass ( "icon basic _deleteParameter" );
	}

	public function _deleteCookie($index, $type = "post") {
		$name = $type . "[" . $index . "]";
		if (isset ( $_COOKIE [$type] [$index] )) {
			\setcookie ( $name, "", \time () - 3600, "/", "127.0.0.1" );
		}
	}

	private function _setPostCookie($content, $method = "post", $index = null) {
		if (isset ( $_COOKIE [$method] )) {
			$cookieValues = \array_values ( $_COOKIE [$method] );
			if ((\array_search ( $content, $cookieValues )) === false) {
				if (! isset ( $index ))
					$index = \sizeof ( $_COOKIE [$method] );
				setcookie ( $method . "[" . $index . "]", $content, \time () + 36000, "/", "127.0.0.1" );
			}
		} else {
			if (! isset ( $index ))
				$index = 0;
			setcookie ( $method . "[" . $index . "]", $content, \time () + 36000, "/", "127.0.0.1" );
		}
	}

	private function _setGetCookie($index, $content) {
		setcookie ( "get[" . $index . "]", $content, \time () + 36000, "/", "127.0.0.1" );
	}

	public function _runAction($frm = null) {
		if (URequest::isPost ()) {
			$url = URequest::cleanUrl ( $_POST ["url"] );
			unset ( $_POST ["url"] );
			$method = $_POST ["method"];
			unset ( $_POST ["method"] );
			$newParams = null;
			$postParams = $_POST;
			if (\sizeof ( $_POST ) > 0) {
				if (\strtoupper ( $method ) === "POST" && $frm !== "frmGetParams") {
					$postParams = [ ];
					$keys = $_POST ["name"];
					$values = $_POST ["value"];
					for($i = 0; $i < \sizeof ( $values ); $i ++) {
						if (JString::isNotNull ( $keys [$i] ))
							$postParams [$keys [$i]] = $values [$i];
					}
					if (\sizeof ( $postParams ) > 0) {
						$this->_setPostCookie ( \json_encode ( $postParams ) );
					}
				} else {
					$newParams = $_POST;
					$this->_setGetCookie ( $url, \json_encode ( $newParams ) );
				}
			}
			$modal = $this->jquery->semantic ()->htmlModal ( "response", \strtoupper ( $method ) . ":" . $url );
			$params = $this->getRequiredRouteParameters ( $url, $newParams );
			if (\sizeof ( $params ) > 0) {
				$toPost = \array_merge ( $postParams, [ "method" => $method,"url" => $url ] );
				$frm = $this->jquery->semantic ()->htmlForm ( "frmGetParams" );
				$frm->addMessage ( "msg", "You must complete the following parameters before continuing navigation testing", "Required URL parameters", "info circle" );
				$paramsValues = $this->_getParametersFromCookie ( $url, $params );
				foreach ( $paramsValues as $param => $value ) {
					$frm->addInput ( $param, \ucfirst ( $param ) )->addRule ( "empty" )->setValue ( $value );
				}
				$frm->setValidationParams ( [ "on" => "blur","inline" => true ] );
				$frm->setSubmitParams ( $this->_getAdminFiles ()->getAdminBaseRoute () . "/_runAction", "#modal", [ "params" => \json_encode ( $toPost ) ] );
				$modal->setContent ( $frm );
				$modal->addAction ( "Validate" );
				$this->jquery->click ( "#action-response-0", "$('#frmGetParams').form('submit');" );
			} else {
				$this->jquery->ajax ( $method, $url, '#content-response.content', [ "params" => \json_encode ( $postParams ) ] );
			}
			$modal->addAction ( "Close" );
			$this->jquery->exec ( "$('.dimmer.modals.page').html('');$('#response').modal('show');", true );
			echo $modal;
			echo $this->jquery->compile ( $this->view );
		}
	}

	private function _getParametersFromCookie($url, $params) {
		$result = \array_fill_keys ( $params, "" );
		if (isset ( $_COOKIE ["get"] )) {
			if (isset ( $_COOKIE ["get"] [$url] )) {
				$values = \json_decode ( $_COOKIE ["get"] [$url], true );
				foreach ( $params as $p ) {
					$result [$p] = @$values [$p];
				}
			}
		}
		return $result;
	}

	private function getRequiredRouteParameters(&$url, $newParams = null) {
		$url = stripslashes ( $url );
		$route = Router::getRouteInfo ( $url );
		if ($route === false) {
			$ns = Startup::getNS ();
			$u = \explode ( "/", $url );
			$controller = $ns . $u [0];
			if (\sizeof ( $u ) > 1)
				$action = $u [1];
			else
				$action = "index";
			if (isset ( $newParams ) && \sizeof ( $newParams ) > 0) {
				$url = $u [0] . "/" . $action . "/" . \implode ( "/", \array_values ( $newParams ) );
				return [ ];
			}
		} else {
			if (isset ( $newParams ) && \sizeof ( $newParams ) > 0) {
				$routeParameters = $route ["parameters"];
				$i = 0;
				foreach ( $newParams as $v ) {
					if (isset ( $routeParameters [$i] ))
						$result [( int ) $routeParameters [$i ++]] = $v;
				}
				ksort ( $result );

				$url = vsprintf ( \preg_replace ( '#\([^\)]+\)#', '%s', $url ), $result );
				return [ ];
			}
			$controller = $route ["controller"];
			$action = $route ["action"];
		}
		if (\class_exists ( $controller )) {
			if (\method_exists ( $controller, $action )) {
				$method = new \ReflectionMethod ( $controller, $action );
				return \array_map ( function ($e) {
					return $e->name;
				}, \array_slice ( $method->getParameters (), 0, $method->getNumberOfRequiredParameters () ) );
			}
		}
		return [ ];
	}

	protected function _createController($controllerName, $variables = [], $ctrlTemplate = 'controller.tpl', $hasView = false, $jsCallback = "") {
		$message = "";
		$frameworkDir = Startup::getFrameworkDir ();
		$controllersNS = \rtrim ( Startup::getNS ( "controllers" ), "\\" );
		$controllersDir = ROOT . DS . str_replace ( "\\", DS, $controllersNS );
		$controllerName = \ucfirst ( $controllerName );
		$filename = $controllersDir . DS . $controllerName . ".php";
		if (\file_exists ( $filename ) === false) {
			if ($controllersNS !== "") {
				$namespace = "namespace " . $controllersNS . ";";
			}
			$msgView = "";
			$indexContent = "";
			if ($hasView) {
				$viewDir = ROOT . DS . "views" . DS . $controllerName . DS;
				UFileSystem::safeMkdir ( $viewDir );
				$viewName = $viewDir . DS . "index.html";
				UFileSystem::openReplaceWriteFromTemplateFile ( $frameworkDir . "/admin/templates/view.tpl", $viewName, [ "%controllerName%" => $controllerName,"%actionName%" => "index" ] );
				$msgView = "<br>The default view associated has been created in <b>" . UFileSystem::cleanPathname ( ROOT . DS . $viewDir ) . "</b>";
				$indexContent = "\$this->loadView(\"" . $controllerName . "/index.html\");";
			}
			$variables = \array_merge ( $variables, [ "%controllerName%" => $controllerName,"%indexContent%" => $indexContent,"%namespace%" => $namespace ] );
			UFileSystem::openReplaceWriteFromTemplateFile ( $frameworkDir . "/admin/templates/" . $ctrlTemplate, $filename, $variables );
			$msgContent = "The <b>" . $controllerName . "</b> controller has been created in <b>" . UFileSystem::cleanFilePathname( $filename ) . "</b>." . $msgView;
			if (isset ( $variables ["%path%"] ) && $variables ["%path%"] !== "") {
				$msgContent .= $this->_addMessageForRouteCreation ( $variables ["%path%"], $jsCallback );
			}
			USession::addOrRemoveValueFromArray("filtered-controllers",$controllersNS."\\".$controllerName);
			$message = $this->showSimpleMessage ( $msgContent, "success", null,"checkmark circle", NULL, "msgGlobal" );
		} else {
			$message = $this->showSimpleMessage ( "The file <b>" . $filename . "</b> already exists.<br>Can not create the <b>" . $controllerName . "</b> controller!", "warning",null, "warning circle", 100000, "msgGlobal" );
		}
		return $message;
	}
	
	protected function _addMessageForRouteCreation($path, $jsCallback = "") {
		$msgContent = "<br>Created route : <b>" . $path . "</b>";
		$msgContent .= "<br>You need to re-init Router cache to apply this update:";
		$btReinitCache = new HtmlButton ( "bt-init-cache", "(Re-)Init router cache", "orange" );
		$btReinitCache->addIcon ( "refresh" );
		$msgContent .= "&nbsp;" . $btReinitCache;
		$this->jquery->getOnClick ( "#bt-init-cache", $this->_getAdminFiles ()->getAdminBaseRoute () . "/_refreshCacheControllers", "#messages", [ "attr" => "","hasLoader" => false,"dataType" => "html","jsCallback" => $jsCallback ] );
		return $msgContent;
	}

	protected function getAdminData() {
		return new CRUDDatas();
	}

	protected function getUbiquityMyAdminViewer() {
		return new UbiquityMyAdminViewer ( $this );
	}
	
	protected function getUbiquityMyAdminModelViewer() {
		return new ModelViewer( $this );
	}

	protected function getUbiquityMyAdminFiles() {
		return new UbiquityMyAdminFiles ();
	}

	private function getSingleton($value, $method) {
		if (! isset ( $value )) {
			$value = $this->$method ();
		}
		return $value;
	}

	/**
	 *
	 * @return CRUDDatas
	 */
	public function _getAdminData():CRUDDatas {
		return $this->getSingleton ( $this->adminData, "getAdminData" );
	}
	
	/**
	 * @return ModelViewer
	 */
	public function _getModelViewer(){
		return $this->getSingleton($this->adminModelViewer, "getUbiquityMyAdminModelViewer");
	}

	/**
	 *
	 * @return UbiquityMyAdminViewer
	 */
	public function _getAdminViewer() {
		return $this->getSingleton ( $this->adminViewer, "getUbiquityMyAdminViewer" );
	}

	/**
	 *
	 * @return UbiquityMyAdminFiles
	 */
	public function _getAdminFiles() {
		return $this->getSingleton ( $this->adminFiles, "getUbiquityMyAdminFiles" );
	}

	protected function getTableNames() {
		return $this->_getAdminData ()->getTableNames ();
	}
	
	public function _getBaseRoute(){
		return $this->_getAdminFiles ()->getAdminBaseRoute ();
	}
	public function _getInstancesFilter($model) {
		return "1=1";
	}

}
