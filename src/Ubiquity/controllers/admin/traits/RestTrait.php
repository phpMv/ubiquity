<?php

namespace Ubiquity\controllers\admin\traits;

use Ajax\semantic\html\collections\HtmlMessage;
use Ajax\semantic\html\collections\form\HtmlForm;
use Ajax\semantic\html\elements\HtmlIconGroups;
use Ajax\semantic\html\elements\HtmlLabel;
use Ajax\service\JString;
use Ubiquity\annotations\parser\DocParser;
use Ubiquity\cache\CacheManager;
use Ubiquity\controllers\Startup;
use Ubiquity\controllers\admin\utils\Constants;
use Ubiquity\controllers\rest\RestServer;
use Ubiquity\exceptions\UbiquityException;
use Ubiquity\utils\base\UString;
use Ubiquity\utils\http\URequest;
use Ubiquity\controllers\rest\RestBaseController;
use Ubiquity\controllers\rest\HasResourceInterface;
use Ubiquity\controllers\rest\RestController;
use Ubiquity\controllers\rest\api\jsonapi\JsonApiRestController;
use Ubiquity\controllers\rest\SimpleRestController;
use Ubiquity\utils\base\UArray;

/**
 *
 * @property \Ubiquity\views\View $view
 * @property \Ajax\JsUtils $jquery
 * @property \Ubiquity\scaffolding\AdminScaffoldController $scaffold
 *
 */
trait RestTrait {

	abstract public function _getFiles();

	abstract public function _getAdminViewer();

	/**
	 *
	 * @param string $content
	 * @param string $type
	 * @param string $icon
	 * @param int $timeout
	 * @param string $staticName
	 * @return HtmlMessage
	 */
	abstract protected function showSimpleMessage($content, $type, $title = null, $icon = "info", $timeout = NULL, $staticName = null): HtmlMessage;

	public function initRestCache($refresh = true) {
		$config = Startup::getConfig ();
		\ob_start ();
		CacheManager::initCache ( $config, "rest" );
		CacheManager::initCache ( $config, "controllers" );
		$message = \ob_get_clean ();
		echo $this->showSimpleMessage ( \nl2br ( $message ), "info", "Rest", "info", 4000 );
		if ($refresh === true)
			$this->_refreshRest ( true );
		echo $this->jquery->compile ( $this->view );
	}

	public function _refreshRest($refresh = false) {
		$result = "";
		try {
			$restRoutes = CacheManager::getRestRoutes ();
			if (\sizeof ( $restRoutes ) > 0) {
				$result = $this->_getAdminViewer ()->getRestRoutesTab ( $restRoutes );
			} else {
				$result = $this->showSimpleMessage ( "No resource Rest found. You can add a new resource.", "", "Rest", "warning circle", null, "tabsRest" );
			}
		} catch ( UbiquityException $e ) {
			$result .= $this->showSimpleMessage ( \nl2br ( $e->getMessage () ), "error", "Rest error", "warning circle", null, "tabsRest" );
		}
		$this->_addRestDataTableBehavior ();
		if ($refresh) {
			echo $result;
		}
	}

	public function _displayRestFormTester() {
		$path = $_POST ["path"] ?? '';
		$resource = $_POST ["resource"] ?? '';
		$controller = $_POST ["controller"] ?? '';
		$controller = \urldecode ( $controller );
		$action = $_POST ["action"] ?? '';
		$msgHelp = $this->_displayActionDoc ( $controller, $action );
		$frm = $this->jquery->semantic ()->htmlForm ( "frmTester-" . $path );
		$pathId = JString::cleanIdentifier ( $path );
		$containerId = "div-tester-" . $pathId;
		$input = $frm->addInput ( "path", null, "text", $path );
		$pathField = $input->getDataField ()->setIdentifier ( "path-" . $path )->addClass ( "_path" );
		$dd = $input->addDropdown ( "GET", Constants::REQUEST_METHODS );
		$methodField = $dd->setIdentifier ( "dd-method-" . $path )->getDataField ()->setProperty ( "name", "method" );
		$methodField->setIdentifier ( "method-" . $path )->addClass ( "_method" );
		$input->addAction ( "Headers...", "right", "barcode" )->addClass ( "basic _requestWithHeaders" )->setTagName ( "div" );
		$input->addAction ( "Parameters...", "right", "settings" )->addClass ( "basic _requestWithParams" )->setTagName ( "div" );
		$btGo = $input->addAction ( "Send" )->setColor ( "blue" );
		$btGo->addIcon ( "send" );
		$btGo->setIdentifier ( "btGo-" . $path );

		$frmHeaders = new HtmlForm ( "frm-headers-" . $path );
		$frmParameters = new HtmlForm ( "frm-parameters-" . $path );

		$this->jquery->postOnClick ( "#" . $btGo->getIdentifier (), $this->_getFiles ()->getAdminBaseRoute () . "/_runRestMethod", "{payload:$(\"#ck-payload-" . $pathId . "\").is(':checked'),pathId: '" . $path . "',path: $('#" . $pathField->getIdentifier () . "').val(),method: $('#" . $methodField->getIdentifier () . "').val(),headers:$('#" . $frmHeaders->getIdentifier () . "').serialize(),params:$('#" . $frmParameters->getIdentifier () . "').serialize()}", "#" . $containerId . " ._runRestMethod", ["hasLoader"=>"internal" ] );
		$this->jquery->postOnClick ( "#" . $containerId . " ._requestWithParams", $this->_getFiles ()->getAdminBaseRoute () . "/_runPostWithParams/_/parameter/rest", "{actualParams:$('#" . $frmParameters->getIdentifier () . "').serialize(),model: '" . $resource . "',toUpdate:'" . $frmParameters->getIdentifier () . "',method:$('#" . $containerId . " ._method').val(),url:$('#" . $containerId . " ._path').val()}", "#modal", [
																																																																																																											"attr" => "",
																																																																																																											"hasLoader" => false ] );
		$this->jquery->postOnClick ( "#" . $containerId . " ._requestWithHeaders", $this->_getFiles ()->getAdminBaseRoute () . "/_runPostWithParams/_/header/rest", "{actualParams: $('#" . $frmHeaders->getIdentifier () . "').serialize(),model: '" . $resource . "',toUpdate:'" . $frmHeaders->getIdentifier () . "',method:$('#" . $containerId . " ._method').val(),url:$('#" . $containerId . " ._path').val()}", "#modal", [
																																																																																																									"attr" => "",
																																																																																																									"hasLoader" => false ] );
		if (! $msgHelp->_empty) {
			$this->jquery->exec ( '$("#' . JString::cleanIdentifier ( "help-" . $action . $controller ) . '").transition("show");', true );
		}
		$this->jquery->compile ( $this->view );
		$this->loadView ( $this->_getFiles ()->getViewRestFormTester (), [ "frmHeaders" => $frmHeaders,"frmParameters" => $frmParameters,"frmTester" => $frm,"pathId" => $pathId,"msgHelp" => $msgHelp ] );
	}

	protected function _displayActionDoc($controller, $action) {
		$docParser = DocParser::docMethodParser ( $controller, $action );
		$msg = $this->showSimpleMessage ( $docParser->getDescriptionAsHtml (), "", "", "help circle blue", null, "msg-help-" . $action . $controller );
		$msg->addHeader ( "Method " . $action );
		$msg->addList ( $docParser->getMethodParamsReturnAsHtml () );
		$msg->addClass ( "hidden transition" );
		$msg->_empty = $docParser->isEmpty ();
		return $msg;
	}

	public function _frmNewResource() {
		$config = Startup::getConfig ();
		$frm = $this->jquery->semantic ()->htmlForm ( "frmNewResource" );
		$frm->addMessage ( "msg", "Creating a new REST controller...", "New resource", HtmlIconGroups::corner ( "heartbeat", "plus" ) );
		$fields = $frm->addFields ();
		$input = $fields->addInput ( "ctrlName", "Controller name" )->addRule ( "empty" );
		$input->labeled ( RestServer::getRestNamespace () . "\\" );
		$baseClasses = array_merge ( [ RestBaseController::class,RestController::class,JsonApiRestController::class,SimpleRestController::class ], CacheManager::getControllers ( RestBaseController::class, true, true ) );
		$baseClasses = array_combine ( $baseClasses, $baseClasses );
		$dd = $fields->addDropdown ( "baseClass", $baseClasses, "Base class", RestController::class );
		$dd->getField ()->each ( function ($index, $item) {
			$class = $item->getProperty ( "data-value" );
			if (is_subclass_of ( $class, HasResourceInterface::class, true )) {
				$item->setProperty ( "data-resource", 'true' );
			}
		} );
		$dd->getField ()->onClick ( "\$('#field-resource').toggle('true'==$(event.target).attr('data-resource'));" );
		$fields = $frm->addFields ();
		$resources = CacheManager::getModels ( $config, true );
		$resources = \array_combine ( $resources, $resources );
		$fields->addInput ( "route", "Main route path", "text", "/rest/" )->addRule ( "empty" );
		$fields->addDropdown ( "resource", $resources, "Resource", end ( $resources ) )->addRule ( [ "exactCount[1]" ] );
		$frm->addCheckbox ( "re-init", "Re-init Rest cache (recommanded)", "reInit" )->setChecked ( true );

		$frm->addDivider ();
		$fields = $frm->addFields ();
		$bt = $fields->addButton ( "bt-create-new-resource", "Create new controller", "teal" );
		$bt->addIcon ( "plus" );
		$fields->addButton ( "bt-cancel-new-resource", "Cancel", "", "$('#frmNewResource').hide();$('#divRest').show();" );
		$frm->setValidationParams ( [ "on" => "blur","inline" => false ] );
		$frm->addErrorMessage ();
		$frm->setSubmitParams ( $this->_getFiles ()->getAdminBaseRoute () . "/_createNewResource", "#divRest", [ "dataType" => "html" ] );
		$this->jquery->exec ( "$('#divRest').hide();$('#div-new-resource').show();", true );
		echo $frm->compile ( $this->jquery, $this->view );
		echo $this->jquery->compile ( $this->view );
	}

	public function _createNewResource() {
		if (URequest::isPost ()) {
			if (isset ( $_POST ["ctrlName"] ) && $_POST ["ctrlName"] !== "") {
				$this->scaffold->addRestController ( ucfirst ( $_POST ["ctrlName"] ), $_POST ["baseClass"], UString::doubleBackSlashes ( $_POST ["resource"] ?? ''), $_POST ["route"], isset ( $_POST ["re-init"] ) );
			}
			$this->jquery->exec ( "$('#div-new-resource').hide();$('#divRest').show();", true );
			echo $this->jquery->compile ( $this->view );
		}
	}

	protected function _addRestDataTableBehavior() {
		$this->jquery->click ( "._toTest", "if(!$(this).hasClass('active')){
					\$(this).closest('tr').after('<tr class=\"active\"><td id=\"sub-td'+$(this).closest('tr').attr('id')+'\" colspan=\"'+$(this).closest('tr').children('td').length+'\">test</td></tr>');
					$(this).addClass('active').removeClass('visibleover');}else{
						$(this).removeClass('active').addClass('visibleover');
						$(this).closest('tr').find('.ui.icon.help').transition('hide');
						$('#sub-td'+$(this).closest('tr').attr('id')).remove();
					}", false, false, true );
		$this->jquery->click ( "._showMsgHelp", '$("#"+$(this).attr("data-show")).transition();' );
		$this->jquery->postOnClick ( "._toTest", $this->_getFiles ()->getAdminBaseRoute () . "/_displayRestFormTester", "{resource:$(this).attr('data-resource'),controller:$(this).attr('data-controller'),action:$(this).attr('data-action'),path:$(this).closest('tr').attr('data-ajax')}", "'#sub-td'+$(self).closest('tr').attr('id')", [
																																																																																				"ajaxTransition" => "random",
																																																																																				"stopPropagation" => true,
																																																																																				"jsCondition" => "!$(self).hasClass('active')" ] );
		$this->jquery->exec ( "addToken=function(jqXHR){
			if(jqXHR.getResponseHeader('authorization')!=null && jqXHR.getResponseHeader('authorization').trim().startsWith('Bearer')){
				var bearer=jqXHR.getResponseHeader('authorization').trim().slice(7);
				$('#access-token').val(bearer);
				$('#access-token').trigger('change');
			}
		}", true );
	}

	public function _runRestMethod() {
		$headers = $this->getRestRequestHeaders ();
		$method = $_POST ["method"];
		$path = $_POST ["path"];
		$payload = UString::isBooleanTrue ( $_POST ["payload"] ?? false);
		$formId = "sub-tddtRest-tr-" . JString::cleanIdentifier ( $_POST ["pathId"] );
		$parameters = [
						"jsCallback" => "$('#" . $formId . " ._restResponse').html(JSON.stringify(data,null,2))",
						"complete" => "var status = { 200 : 'green', 401 : 'orange', 403 : 'brown', 404 : 'black', 500 : 'red' };
							var headers=jqXHR.getAllResponseHeaders();
							headers=headers.split(/\\r\\n/);
							var bHeaders=[];
							$.each(headers,function(index,header){
								var vp=header.split(':');
								if(vp[0]!='')
									bHeaders.push('\"'+vp[0]+'\":\"'+vp[1]+'\"');
							});
							headers=$.parseJSON('{'+bHeaders.join(',')+'}');
						$('#" . $formId . " ._responseHeaders').html(JSON.stringify(headers,null,2));
						if(jqXHR.responseText==null){
							$('#" . $formId . " ._restResponse').html('The response is empty');
						}else if(jqXHR.status!=200){
							$('#" . $formId . " ._restResponse').html(jqXHR.responseText);
						}
						$('#" . $formId . " ._statusText').html(jqXHR.statusText);
						$('#" . $formId . " ._status').html(jqXHR.status);
						$('#" . $formId . " ._status').removeClass('red black brown orange green').addClass(status[jqXHR.status]);
						addToken(jqXHR);",
						"dataType" => "json",
						"headers" => $headers,
						"params" => $this->getRestRequestParams ()
						];
		if ( $payload===true) {
			$parameters ["contentType"] = "'application/json; charset=utf-8'";
		}else{
			$parameters ["contentType"] = "'application/x-www-form-urlencoded'";
		}
		$this->jquery->ajax ( $method, addslashes($path), "#" . $formId . " ._restResponse", $parameters );
		echo '<div><h5 class="ui top block attached header">Response headers</h5><div class="ui attached segment"><pre style="font-size: 10px;overflow-x: auto;" class="_responseHeaders"></pre></div></div>';
		echo $this->jquery->compile ( $this->view );
	}

	protected function getRestRequestHeaders() {
		$result = [ "Authorization" => "js:'Bearer '+$('#access-token').val()" ];
		if (isset ( $_POST ["headers"] )) {
			$headers = urldecode ( $_POST ["headers"] );
			\parse_str ( $headers, $output );
			$this->_getParamsForJSON ( $result, $output );
		}
		if(UArray::isAssociative($result)){
			return UArray::toJSON( $result );
		}
		return "{" . \implode ( ",", $result ) . "}";
	}

	protected function getRestRequestParams() {
		$result = [ ];
		if (isset ( $_POST ["params"] )) {
			$headers = urldecode ( $_POST ["params"] );
			\parse_str ( $headers, $output );
			$this->_getParamsForJSON ( $result, $output );
		}
		if(UArray::isAssociative($result)){
			return json_encode ( $result );
		}
		return "{" . \implode ( ",", $result ) . "}";
	}

	protected function _getParamsForJSON(&$result, $params) {
		if (isset ( $params ["name"] )) {
			$names = $params ["name"];
			$values = $params ["value"];
			$count = \sizeof ( $names );
			for($i = 0; $i < $count; $i ++) {
				$name = $names [$i];
				if (UString::isNotNull ( $name )) {
					if (isset ( $values [$i] )) {
						$corrValue = str_replace ( "'", '"', $values [$i] );
						$v=UString::isJson ( $corrValue );
						if ($v) {
							$result [$name] = json_decode ( $corrValue, true );
						} else {
							$result [] = '"' . $name . '": "' . \addslashes ( $values [$i] ) . '"';
						}
					}
				}
			}
		}
	}

	public function _saveToken() {
		if (isset ( $_POST ["_token"] ))
			$_SESSION ["_token"] = $_POST ["_token"];
	}

	public function _saveRequestParams($type = "parameter") {
		$keys = $_POST ["name"];
		$values = $_POST ["value"];
		$toUpdate = $_POST ["toUpdate"];
		$frm = $this->jquery->semantic ()->htmlForm ( $toUpdate );
		$frm->setSize ( "mini" );
		$count = \sizeof ( $values );
		for($i = 0; $i < $count; $i ++) {
			if (JString::isNull ( $keys [$i] )) {
				unset ( $keys [$i] );
				unset ( $values [$i] );
			}
		}
		$keys = \array_values ( $keys );
		$values = \array_values ( $values );
		$count = \sizeof ( $values );
		if ($count > 0) {
			$fields = $frm->addFields ();
			$fields->addElement ( "", "Name", "", "div", "ui label mini black pointing below" );
			$fields->addElement ( "", "Value", "", "div", "ui label mini black pointing below" );
			for($i = 0; $i < $count; $i ++) {
				$fields = $frm->addFields ();
				$fields->addInput ( "name[]", "", "text", $keys [$i] )->setIdentifier ( "name-" . $i );
				$input = $fields->addInput ( "value[]", "", "text", $values [$i] )->setIdentifier ( "value-" . $i );
				$input->addAction ( "", true, "remove" )->addClass ( "icon basic mini _deleteParameter" );
			}
		} else {
			$frm->addItem ( new HtmlLabel ( "", "No " . $type . "s" ) );
		}
		$this->jquery->click ( "._deleteParameter", "
								$(this).parents('.fields').remove();
								if($('#" . $toUpdate . "').find('.fields').length==1){
									$('#" . $toUpdate . "').children('.fields').remove();
									$('#" . $toUpdate . "').append('<div class=\"ui label\">No " . $type . "s</div>');
								}
					", true, true, true );
		echo $frm;
		echo $this->jquery->compile ( $this->view );
	}
}
