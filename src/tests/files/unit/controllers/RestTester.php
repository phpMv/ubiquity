<?php

namespace controllers;

use Ubiquity\utils\http\URequest;

/**
 * Controller RestTester
 *
 * @property \Ajax\JsUtils $jquery
 */
class RestTester extends ControllerBase {

	public function index() {
		$form = $this->jquery->semantic ()->htmlForm ( "frm" );
		$fields = $form->addFields ();
		$fields->addInput ( 'url' );
		$fields->addInput ( 'method' );
		$fields = $form->addFields ();
		$fields->addInput ( 'datas' );
		$fields->addInput ( 'contentType', null, 'text', 'application/x-www-form-urlencoded' );
		$form->addSubmit ( 'btSubmit', 'Valider', 'green', "RestTester/submit", "#request" );
		$this->jquery->compile ( $this->view );
		$this->loadView ( 'RestTester/index.html' );
	}

	public function submit() {
		$url = addslashes ( URequest::post ( 'url' ) );
		$method = URequest::post ( 'method', 'get' );
		$datas = URequest::post ( 'datas' );
		if ($datas == null) {
			$datas = '{}';
		}
		$contentType = URequest::post ( 'contentType', 'application/x-www-form-urlencoded' );
		$this->jquery->ajax ( $method, $url, '#response', [ 'complete' => "$('#status').html(jqXHR.status);",'jsCallback' => "if(data.data.id)$('#newId').html(data.data.id);$('#response').html(JSON.stringify(data,undefined,2));",'params' => $datas,'contentType' => "'" . $contentType . "'" ] );
		echo $this->jquery->compile ();
	}
}
