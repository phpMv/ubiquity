<?php

namespace Ubiquity\controllers\crud;

use Ubiquity\orm\DAO;
use Ubiquity\controllers\ControllerBase;
use Ubiquity\controllers\crud\interfaces\HasModelViewerInterface;
use Ubiquity\controllers\semantic\MessagesTrait;
use Ubiquity\utils\http\URequest;
use Ubiquity\utils\http\UResponse;
use Ubiquity\orm\OrmUtils;
use Ubiquity\utils\base\UString;
use Ajax\semantic\html\collections\HtmlMessage;
use Ajax\common\html\HtmlContentOnly;
use Ubiquity\controllers\semantic\InsertJqueryTrait;
use Ajax\semantic\widgets\datatable\DataTable;

/**
 * Ubiquity\controllers\crud$CRUDController
 * This class is part of Ubiquity
 *
 * @author jc
 * @version 1.0.3
 *
 */
abstract class CRUDController extends ControllerBase implements HasModelViewerInterface {
	use MessagesTrait,CRUDControllerUtilitiesTrait,InsertJqueryTrait;
	protected $model;
	protected $activePage=1;
	protected $style;
	
	public function __construct() {
		parent::__construct ();
		DAO::$transformerOp = 'toView';
		$this->style = '';
		$this->_insertJquerySemantic ();
	}
	
	/**
	 * Return a JSON representation of $instances for the JsonDataTable component
	 * @param array $instances
	 * @return string
	 */
	protected function refreshAsJson($instances){
		$objects = \array_map ( function ($o) {
			return $o->_rest;
		}, $instances );
			return \json_encode(\array_values ( $objects ));
	}
	
	public function _setStyle($elm) {
		if ($this->style === 'inverted') {
			$elm->setInverted ( true );
			if ($elm instanceof DataTable) {
				$elm->setActiveRowSelector ( 'black' );
			}
		}
	}
	
	/**
	 * Default page : list all objects
	 * Uses modelViewer.isModal, modelViewer.getModelDataTable
	 * Uses CRUDFiles.getViewIndex template (default : @framework/crud/index.html)
	 * Triggers the events onDisplayElements,beforeLoadView
	 */
	public function index() {
		$objects = $this->getInstances ( $totalCount ,$this->activePage);
		$modal = ($this->_getModelViewer ()->isModal ( $objects, $this->model )) ? "modal" : "no";
		$dt = $this->_getModelViewer ()->getModelDataTable ( $objects, $this->model, $totalCount ,$this->activePage);
		$this->jquery->getOnClick ( '#btAddNew', $this->_getBaseRoute () . '/newModel/' . $modal, '#frm-add-update', [ 'hasLoader' => 'internal' ] );
		$this->_getEvents ()->onDisplayElements ( $dt, $objects, false );
		$this->crudLoadView ( $this->_getFiles ()->getViewIndex (), [ 'classname' => $this->model,'messages' => $this->jquery->semantic ()->matchHtmlComponents ( function ($compo) {
			return $compo instanceof HtmlMessage;
		} ) ] );
	}

	/**
	 * @param $member
	 * @param boolean $callback
	 * @throws \Exception
	 *
	 * @post
	 */
	#[\Ubiquity\attributes\items\router\Post]
	public function updateMember($member, $callback = false) {
		$instance = $_SESSION ['instance'] ?? null;
		if (isset ( $instance )) {
			$this->_getEvents ()->onBeforeUpdateRequest ( $_POST, false );
			$updated = CRUDHelper::update ( $instance, $_POST, true, true, function ($inst) {
				$this->_getEvents ()->onBeforeUpdate ( $inst, false );
			} );
				if ($updated) {
					if ($callback === false) {
						$dt = $this->_getModelViewer ()->getModelDataTable ( [ $instance ], $this->model, 1 );
						$dt->setGroupByFields(null);
						$dt->compile ();
						echo new HtmlContentOnly ( $dt->getFieldValue ( $member ) );
					} else {
						if (\method_exists ( $this, $callback )) {
							$this->$callback ( $member, $instance );
						} else {
							throw new \Exception ( "The method `" . $callback . "` does not exists in " . get_class () );
						}
					}
				} else {
					UResponse::setResponseCode ( 404 );
				}
		} else {
			throw new \Exception ( '$_SESSION["instance"] is null' );
		}
	}
	
	/**
	 * Refreshes the area corresponding to the DataTable
	 */
	public function refresh_() {
		$model = $this->model;
		if (isset ( $_POST ['s'] )) {
			$instances = $this->search ( $model, $_POST ['s'] );
		} else {
			$page = URequest::post ( 'p', 1 );
			$instances = $this->getInstances ( $totalCount, $page );
		}
		if (! isset ( $totalCount )) {
			$totalCount = DAO::count ( $model, $this->_getAdminData ()->_getInstancesFilter ( $model ) );
		}
		$recordsPerPage = $this->_getModelViewer ()->recordsPerPage ( $model, $totalCount );
		$grpByFields = $this->_getModelViewer ()->getGroupByFields ();
		if (isset ( $recordsPerPage )) {
			if (! is_array ( $grpByFields )) {
				UResponse::asJSON ();
				echo $this->refreshAsJson($instances);
			} else {
				$this->_renderDataTableForRefresh ( $instances, $model, $totalCount );
			}
		} else {
			$this->jquery->execAtLast ( '$("#search-query-content").html("' . $_POST ['s'] . '");$("#search-query").show();$("#table-details").html("");' );
			$this->_renderDataTableForRefresh ( $instances, $model, $totalCount );
		}
	}
	
	/**
	 * Edits an instance
	 *
	 * @param string $modal Accept "no" or "modal" for a modal dialog
	 * @param string $ids the primary value(s)
	 */
	public function edit($modal = "no", $ids = "") {
		if (URequest::isAjax ()) {
			$instance = $this->getModelInstance ( $ids, false );
			$instance->_new = false;
			$this->_edit ( $instance, $modal );
		} else {
			$this->jquery->execAtLast ( "$('._edit[data-ajax={$ids}]').trigger('click');" );
			$this->index ();
		}
	}
	
	/**
	 * Adds a new instance and edits it
	 *
	 * @param string $modal Accept "no" or "modal" for a modal dialog
	 */
	public function newModel($modal = "no") {
		if (URequest::isAjax ()) {
			$model = $this->model;
			$instance = new $model ();
			$this->_getEvents()->onNewInstance($instance);
			$instance->_new = true;
			$this->_edit ( $instance, $modal );
		} else {
			$this->jquery->execAtLast ( "$('.ui.button._new').trigger('click');" );
			$this->index ();
		}
	}

	/**
	 * @param $member
	 * @throws \Exception
	 *
	 * @post
	 */
	#[\Ubiquity\attributes\items\router\Post]
	public function editMember($member) {
		$ids = URequest::post ( "id" );
		$td = URequest::post ( "td" );
		$part = URequest::post ( "part" );
		$instance = $this->getModelInstance ( $ids, false, $member );
		$_SESSION ["instance"] = $instance;
		$instance->_new = false;
		$form = $this->_getModelViewer ()->getMemberForm ( "frm-member-" . $member, $instance, $member, $td, $part, 'updateMember' );
		$form->setLibraryId ( "_compo_" );
		$this->jquery->renderView ( "@framework/main/component.html" );
	}
	
	/**
	 * Displays an instance
	 *
	 * @param string $modal
	 * @param string $ids
	 */
	public function display($modal = "no", $ids = "") {
		if (URequest::isAjax ()) {
			$instance = $this->getModelInstance ( $ids );
			$key = OrmUtils::getFirstKeyValue ( $instance );
			$this->jquery->execOn ( "click", "._close", '$("#table-details").html("");$("#dataTable").show();' );
			$this->jquery->getOnClick ( "._edit", $this->_getBaseRoute () . "/edit/" . $modal . "/" . $key, "#frm-add-update" );
			$this->jquery->getOnClick ( "._delete", $this->_getBaseRoute () . "/delete/" . $key, "#table-messages" );
			
			$this->_getModelViewer ()->getModelDataElement ( $instance, $this->model, $modal );
			$this->jquery->renderView ( $this->_getFiles ()->getViewDisplay (), [ "classname" => $this->model,"instance" => $instance,"pk" => $key ] );
		} else {
			$this->jquery->execAtLast ( "$('._display[data-ajax={$ids}]').trigger('click');" );
			$this->index ();
		}
	}
	
	/**
	 * Deletes an instance
	 *
	 * @param mixed $ids
	 * @route("methods"=>["post","get"])
	 */
	#[\Ubiquity\attributes\items\router\Route(methods: ['get','post'])]
	public function delete($ids) {
		if (URequest::isAjax ()) {
			$instance = $this->getModelInstance ( $ids );
			$instanceString = $this->getInstanceToString ( $instance );
			if (\count ( $_POST ) > 0) {
				try {
					if (DAO::remove ( $instance )) {
						$message = new CRUDMessage ( "Deletion of `<b>" . $instanceString . "</b>`", "Deletion", "info", "info circle", 4000 );
						$message = $this->_getEvents ()->onSuccessDeleteMessage ( $message, $instance );
						$this->jquery->exec ( "$('._element[data-ajax={$ids}]').remove();", true );
					} else {
						$message = new CRUDMessage ( "Can not delete `" . $instanceString . "`", "Deletion", "warning", "warning circle" );
						$message = $this->_getEvents ()->onErrorDeleteMessage ( $message, $instance );
					}
				} catch ( \Exception $e ) {
					$message = new CRUDMessage ( "Exception : can not delete `" . $instanceString . "`", "Exception", "warning", "warning" );
					$message = $this->_getEvents ()->onErrorDeleteMessage ( $message, $instance );
				}
				$message = $this->showSimpleMessage_ ( $message );
			} else {
				$message = new CRUDMessage ( "Do you confirm the deletion of `<b>" . $instanceString . "</b>`?", "Remove confirmation", "error", "question circle" );
				$message = $this->_getEvents ()->onConfDeleteMessage ( $message, $instance );
				$message = $this->showConfMessage_ ( $message, $this->_getBaseRoute () . "/delete/{$ids}", "#table-messages", $ids );
			}
			echo $message;
			echo $this->jquery->compile ( $this->view );
		} else {
			$this->jquery->execAtLast ( "$('._delete[data-ajax={$ids}]').trigger('click');" );
			$this->index ();
		}
	}
	
	public function refreshTable($id = null) {
		$compo = $this->_showModel ( $id );
		$this->jquery->execAtLast ( '$("#table-details").html("");' );
		$compo->setLibraryId ( "_compo_" );
		$this->jquery->renderView ( "@framework/main/component.html" );
	}
	
	/**
	 * Updates an instance from the data posted in a form
	 *
	 * @return object The updated instance
	 *
	 * @post
	 */
	#[\Ubiquity\attributes\items\router\Post]
	public function updateModel() {
		$message = new CRUDMessage ( "Modifications were successfully saved", "Updating" );
		$instance = $_SESSION ["instance"] ?? null;
		if (isset ( $instance )) {
			$isNew = $instance->_new;
			try {
				$this->_getEvents ()->onBeforeUpdateRequest ( $_POST, $isNew );
				$updated = CRUDHelper::update ( $instance, $_POST, true, true, function ($inst, $isNew) {
					$this->_getEvents ()->onBeforeUpdate ( $inst, $isNew );
				} );
					if ($updated) {
						$message->setType ( "success" )->setIcon ( "check circle outline" );
						$message = $this->_getEvents ()->onSuccessUpdateMessage ( $message, $instance );
						$this->refreshInstance ( $instance, $isNew );
					} else {
						$message->setMessage ( "An error has occurred. Can not save changes." )->setType ( "error" )->setIcon ( "warning circle" );
						$message = $this->_getEvents ()->onErrorUpdateMessage ( $message, $instance );
					}
			} catch ( \Exception $e ) {
				$instanceString = $this->getInstanceToString ( $instance );
				$message = new CRUDMessage ( "Exception : can not update `" . $instanceString . "`", "Exception", "warning", "warning" );
				$message = $this->_getEvents ()->onErrorUpdateMessage ( $message, $instance );
			}
			echo $this->showSimpleMessage_ ( $message, "updateMsg" );
			echo $this->jquery->compile ( $this->view );
			return $instance;
		} else {
			throw new \Exception ( '$_SESSION["instance"] is null' );
		}
	}
	
	/**
	 * Shows associated members with foreign keys
	 *
	 * @param mixed $ids
	 */
	public function showDetail($ids) {
		if (URequest::isAjax ()) {
			$instance = $this->getModelInstance ( $ids );
			$viewer = $this->_getModelViewer ();
			$hasElements = false;
			$model = $this->model;
			$fkInstances = CRUDHelper::getFKIntances ( $instance, $model );
			$semantic = $this->jquery->semantic ();
			$grid = $semantic->htmlGrid ( 'detail' );
			if (($nb = \count ( $fkInstances )) > 0) {
				$wide = intval ( 16 / $nb );
				if ($wide < 4) {
					$wide = 4;
				}
				foreach ( $fkInstances as $member => $fkInstanceArray ) {
					$element = $viewer->getFkMemberElementDetails ( $member, $fkInstanceArray ['objectFK'], $fkInstanceArray ['fkClass'], $fkInstanceArray ['fkTable'] );
					if (isset ( $element )) {
						$grid->addCol ( $wide )->setContent ( $element );
						$hasElements = true;
					}
				}
				if ($hasElements) {
					echo $grid;
					$url = $this->_getFiles ()->getDetailClickURL ( $this->model );
					if (UString::isNotNull ( $url )) {
						$this->detailClick ( $url );
					}
				}
				echo $this->jquery->compile ( $this->view );
			}
		} else {
			$this->jquery->execAtLast ( "$('tr[data-ajax={$ids}]').trigger('click');" );
			$this->index ();
		}
	}
	
	public function detailClick($url,$responseElement='#divTable',$attributes=[ "attr" => "data-ajax"]) {
		$this->jquery->postOnClick ( ".showTable", $this->_getBaseRoute () . "/" . $url, "{}", $responseElement, $attributes );
	}
}
