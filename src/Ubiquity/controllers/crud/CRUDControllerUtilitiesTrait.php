<?php

namespace Ubiquity\controllers\crud;

use Ubiquity\utils\http\URequest;
use Ubiquity\orm\DAO;
use Ajax\semantic\widgets\datatable\Pagination;
use Ajax\common\html\HtmlContentOnly;
use Ubiquity\orm\OrmUtils;
use Ajax\semantic\html\collections\HtmlMessage;
use Ubiquity\controllers\crud\viewers\ModelViewer;

/**
 *
 * @author jc
 * @property int $activePage
 * @property string $model
 * @property \Ajax\php\ubiquity\JsUtils $jquery
 * @property \Ubiquity\views\View $view
 */
trait CRUDControllerUtilitiesTrait {
	
	abstract protected function showSimpleMessage_(CRUDMessage $message, $staticName = null): HtmlMessage;
	
	abstract public function loadView($viewName, $pData = NULL, $asString = false);
	
	abstract public function index();
	
	abstract public function _getBaseRoute();
	
	abstract protected function showConfMessage_(CRUDMessage $message, $url, $responseElement, $data, $attributes = NULL): HtmlMessage;

	abstract public function _setStyle($elm);
	
	protected $modelViewer;
	protected $adminDatas;
	protected $events;
	protected $crudFiles;
	
	protected function getInstances(&$totalCount, $page = 1, $id = null) {
		$this->activePage = $page;
		$model = $this->model;
		$condition = $this->_getAdminData ()->_getInstancesFilter ( $model );
		$totalCount = DAO::count ( $model, $condition );
		if ($totalCount) {
			$recordsPerPage = $this->_getModelViewer ()->recordsPerPage ( $model, $totalCount );
			if (is_numeric ( $recordsPerPage )) {
				if (isset ( $id )) {
					$rownum = DAO::getRownum ( $model, $id );
					$this->activePage = Pagination::getPageOfRow ( $rownum, $recordsPerPage );
				}
				return DAO::paginate ( $model, $this->activePage, $recordsPerPage, $condition );
			}
		}
		return DAO::getAll ( $model, $condition );
	}
	
	protected function search($model, $search) {
		$fields = $this->_getAdminData ()->getSearchFieldNames ( $model );
		$condition = $this->_getAdminData ()->_getInstancesFilter ( $model );
		return CRUDHelper::search ( $model, $search, $fields, $condition );
	}
	
	/**
	 *
	 * @param mixed $ids
	 * @param boolean $transform
	 * @param boolean $included
	 * @return object
	 */
	private function getModelInstance($ids, $transform = true, $included = true) {
		$ids = \explode ( "_", $ids );
		if (! is_bool ( $included )) {
			if (! is_array ( $included )) {
				$included = [ $included ];
			}
		}
		DAO::$useTransformers = $transform;
		$instance = DAO::getById ( $this->model, $ids, $included );
		if (isset ( $instance )) {
			return $instance;
		}
		$message = new CRUDMessage ( "This object does not exist!", "Get object", "warning", "warning circle" );
		$message = $this->_getEvents ()->onNotFoundMessage ( $message, $ids );
		echo $this->showSimpleMessage_ ( $message );
		echo $this->jquery->compile ( $this->view );
		exit ( 1 );
	}
	
	protected function updateMemberDataElement($member, $instance) {
		$dt = $this->_getModelViewer ()->getModelDataElement ( $instance, $this->model, false );
		$dt->compile ();
		echo new HtmlContentOnly ( $dt->getFieldValue ( $member ) );
	}
	
	private function _renderDataTableForRefresh($instances, $model, $totalCount) {
		$compo = $this->_getModelViewer ()->getModelDataTable ( $instances, $model, $totalCount )->refresh ( [ "tbody" ] );
		$this->_getEvents ()->onDisplayElements ( $compo, $instances, true );
		$compo->setLibraryId ( "_compo_" );
		$this->jquery->renderView ( "@framework/main/component.html" );
	}
	
	protected function _edit($instance, $modal = "no") {
		$_SESSION ["instance"] = $instance;
		$modal = ($modal == "modal");
		$modelViewer=$this->_getModelViewer ();
		$form = $modelViewer->getForm ( "frmEdit", $instance );
		$this->_setStyle($form);
		$this->jquery->click ( "#action-modal-frmEdit-0", "$('#frmEdit').form('submit');", false );
		if (! $modal) {
			$this->jquery->click ( "#bt-cancel", "$('#form-container').transition('drop');" );
			$this->jquery->compile ( $this->view );
			$this->loadView ( $this->_getFiles ()->getViewForm (), [ "modal" => $modal,"instance" => $instance,"isNew" => $instance->_new ] );
		} else {
			$this->jquery->exec ( "$('#modal-frmEdit').modal('show');", true );
			$form = $form->asModal ( $modelViewer->getFormModalTitle($instance) );
			$form->addClass($this->style);
			[$btOkay,$btCancel]=$form->setActions ( [ "Okay_","Cancel" ] );
			$btOkay->addClass ( 'green '.$this->style );
			$btCancel->addClass($this->style);
			$modelViewer->onFormModalButtons($btOkay,$btCancel);
			$form->onHidden ( "$('#modal-frmEdit').remove();" );
			echo $form->compile ( $this->jquery );
			echo $this->jquery->compile ( $this->view );
		}
	}
	
	protected function _showModel($id = null) {
		$model = $this->model;
		$datas = $this->getInstances ( $totalCount, 1, $id );
		return $this->_getModelViewer ()->getModelDataTable ( $datas, $model, $totalCount, $this->activePage );
	}
	
	/**
	 * Helper to delete multiple objects
	 *
	 * @param mixed $data
	 * @param string $action
	 * @param string $target the css selector for refreshing
	 * @param callable|string $condition the callback for generating the SQL where (for deletion) with the parameter data, or a simple string
	 * @param array $params The statement parameters for a prepared query
	 */
	protected function _deleteMultiple($data, $action, $target, $condition, $params = [ ]) {
		if (URequest::isPost ()) {
			if (\is_callable ( $condition )) {
				$condition = $condition ( $data );
			}
			$rep = DAO::deleteAll ( $this->model, $condition, $params );
			if ($rep) {
				$message = new CRUDMessage ( "Deleting {count} objects", "Deletion", "info", "info circle", 4000 );
				$message = $this->_getEvents ()->onSuccessDeleteMultipleMessage ( $message, $rep );
				$message->parseContent ( [ "count" => $rep ] );
			}
			if (isset ( $message )) {
				$this->showSimpleMessage_ ( $message, "delete-all" );
			}
			$this->index ();
		} else {
			$message = new CRUDMessage ( "Do you confirm the deletion of this objects?", "Remove confirmation", "error ".$this->style );
			$this->_getEvents ()->onConfDeleteMultipleMessage ( $message, $data );
			$message = $this->showConfMessage_ ( $message, $this->_getBaseRoute () . "/{$action}/{$data}", $target, $data, [ "jqueryDone" => "replaceWith" ] );
			echo $message;
			echo $this->jquery->compile ( $this->view );
		}
	}
	
	protected function refreshInstance($instance, $isNew) {
		if ($this->_getAdminData ()->refreshPartialInstance () && ! $isNew) {
			$this->jquery->setJsonToElement ( OrmUtils::objectAsJSON ( $instance ) );
		} else {
			$pk = OrmUtils::getFirstKeyValue ( $instance );
			$this->jquery->get ( $this->_getBaseRoute () . "/refreshTable/" . $pk, "#lv", [ "jqueryDone" => "replaceWith" ] );
		}
	}
	
	/**
	 * To override for defining a new adminData
	 *
	 * @return CRUDDatas
	 */
	protected function getAdminData(): CRUDDatas {
		return new CRUDDatas ($this);
	}
	
	public function _getAdminData(): CRUDDatas {
		return $this->getSingleton ( $this->adminDatas, 'getAdminData' );
	}
	
	/**
	 * To override for defining a new ModelViewer
	 *
	 * @return ModelViewer
	 */
	protected function getModelViewer(): ModelViewer {
		return new ModelViewer ( $this ,$this->style??null);
	}
	
	protected function _getModelViewer(): ModelViewer {
		return $this->getSingleton ( $this->modelViewer, 'getModelViewer' );
	}
	
	/**
	 * To override for changing view files
	 *
	 * @return CRUDFiles
	 */
	protected function getFiles(): CRUDFiles {
		return new CRUDFiles ();
	}
	
	/**
	 *
	 * @return CRUDFiles
	 */
	public function _getFiles() {
		return $this->getSingleton ( $this->crudFiles, 'getFiles' );
	}
	
	/**
	 * To override for changing events
	 *
	 * @return CRUDEvents
	 */
	protected function getEvents(): CRUDEvents {
		return new CRUDEvents ( $this );
	}
	
	private function _getEvents(): CRUDEvents {
		return $this->getSingleton ( $this->events, 'getEvents' );
	}
	
	private function getSingleton(&$value, $method) {
		if (! isset ( $value )) {
			$value = $this->$method ();
		}
		return $value;
	}
	
	private function crudLoadView($viewName, $vars = [ ]) {
		$vars['inverted']=$this->style;
		$this->_getEvents ()->beforeLoadView ( $viewName, $vars );
		if (! URequest::isAjax ()) {
			$files = $this->_getFiles ();
			$mainTemplate = $files->getBaseTemplate ();
			if (isset ( $mainTemplate )) {
				$vars ['_viewname'] = $viewName;
				$vars ['_base'] = $mainTemplate;
				$this->jquery->renderView ( $files->getViewBaseTemplate (), $vars );
			} else {
				$vars ['hasScript'] = true;
				$this->jquery->renderView ( $viewName, $vars );
			}
		} else {
			$vars ['hasScript'] = true;
			$this->jquery->renderView ( $viewName, $vars );
		}
	}
	
	/**
	 *
	 * @param object $instance
	 * @return string
	 */
	protected function getInstanceToString($instance) {
		if (\method_exists ( $instance, '__toString' )) {
			return $instance . '';
		} else {
			return \get_class ( $instance );
		}
	}
}

