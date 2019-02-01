<?php

namespace Ubiquity\controllers\crud;

use Ajax\semantic\widgets\datatable\DataTable;

class CRUDEvents {
	
	protected $controller;
	
	public function __construct($controller){
		$this->controller=$controller;
	}
	
	/**
	 * Returns the message displayed after a deletion
	 * @param CRUDMessage $message
	 * @param object $instance
	 * @return CRUDMessage
	 */
	public function onSuccessDeleteMessage(CRUDMessage $message,$instance):CRUDMessage{
		return $message;
	}
	
	/**
	 * Returns the message displayed when an error occurred when deleting
	 * @param CRUDMessage $message
	 * @param object $instance
	 * @return CRUDMessage
	 */
	public function onErrorDeleteMessage(CRUDMessage $message,$instance):CRUDMessage{
		return $message;
	}
	
	/**
	 * Returns the confirmation message displayed before deleting an instance
	 * @param CRUDMessage $message
	 * @param object $instance
	 * @return CRUDMessage
	 */
	public function onConfDeleteMessage(CRUDMessage $message,$instance):CRUDMessage{
		return $message;
	}

	/**
	 * Returns the message displayed when an instance is added or inserted
	 * @param CRUDMessage $message
	 * @param object $instance
	 * @return CRUDMessage
	 */
	public function onSuccessUpdateMessage(CRUDMessage $message,$instance):CRUDMessage{
		return $message;
	}
	
	/**
	 * Returns the message displayed when an error occurred when updating or inserting
	 * @param CRUDMessage $message
	 * @param object $instance
	 * @return CRUDMessage
	 */
	public function onErrorUpdateMessage(CRUDMessage $message,$instance):CRUDMessage{
		return $message;
	}
	
	/**
	 * Returns the message displayed when an instance does not exists
	 * @param CRUDMessage $message
	 * @param mixed $ids
	 * @return CRUDMessage
	 */
	public function onNotFoundMessage(CRUDMessage $message,$ids):CRUDMessage{
		return $message;
	}
	
	/**
	 * @param string $viewName
	 * @param array|null $vars
	 */
	public function beforeLoadView($viewName,&$vars){
		
	}
	
	/**
	 * Triggered after displaying objects in dataTable 
	 * @param DataTable $dataTable
	 * @param array $objects
	 * @param boolean $refresh
	 */
	public function onDisplayElements($dataTable,$objects,$refresh){
		
	}
	
	public function onSuccessDeleteMultipleMessage(CRUDMessage $message,$instance):CRUDMessage{
		return $message;
	}
	
	public function onErrorDeleteMultipleMessage(CRUDMessage $message):CRUDMessage{
		return $message;
	}
	
	public function onConfDeleteMultipleMessage(CRUDMessage $message,$data):CRUDMessage{
		return $message;
	}
}

