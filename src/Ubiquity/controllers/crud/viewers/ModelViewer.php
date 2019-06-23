<?php

namespace Ubiquity\controllers\crud\viewers;

use Ajax\semantic\html\elements\HtmlButton;
use Ajax\semantic\html\elements\HtmlHeader;
use Ajax\semantic\html\elements\HtmlLabel;
use Ajax\semantic\widgets\datatable\DataTable;
use Ajax\semantic\widgets\datatable\PositionInTable;
use Ubiquity\controllers\crud\CRUDHelper;
use Ubiquity\controllers\crud\interfaces\HasModelViewerInterface;
use Ubiquity\controllers\crud\viewers\traits\FormModelViewerTrait;
use Ubiquity\orm\OrmUtils;
use Ubiquity\utils\base\UString;

/**
 * Associated with a CRUDController class
 * Responsible of the display
 *
 * @author jc
 *
 */
class ModelViewer {
	use FormModelViewerTrait;
	/**
	 *
	 * @var \Ajax\JsUtils
	 */
	private $jquery;

	/**
	 *
	 * @var HasModelViewerInterface
	 */
	protected $controller;

	public function __construct(HasModelViewerInterface $controller) {
		$this->jquery = $controller->jquery;
		$this->controller = $controller;
	}

	/**
	 * Returns a DataElement object for displaying the instance
	 * Used in the display method of the CrudController
	 * in display route
	 *
	 * @param object $instance
	 * @param string $model The model class name (long name)
	 * @param boolean $modal
	 * @return \Ajax\semantic\widgets\dataelement\DataElement
	 */
	public function getModelDataElement($instance, $model, $modal) {
		$semantic = $this->jquery->semantic ();
		$fields = $this->controller->_getAdminData ()->getElementFieldNames ( $model );

		$dataElement = $semantic->dataElement ( "de", $instance );
		$pk = OrmUtils::getFirstKeyValue ( $instance );
		$dataElement->getInstanceViewer ()->setIdentifierFunction ( function () use ($pk) {
			return $pk;
		} );
		$dataElement->setFields ( $fields );
		$dataElement->setCaptions ( $this->getElementCaptions ( $fields, $model, $instance ) );

		$fkInstances = CRUDHelper::getFKIntances ( $instance, $model );
		foreach ( $fkInstances as $member => $fkInstanceArray ) {
			if (array_search ( $member, $fields ) !== false) {
				$dataElement->setValueFunction ( $member, function () use ($fkInstanceArray, $member) {
					return $this->getFkMemberElement ( $member, $fkInstanceArray ["objectFK"], $fkInstanceArray ["fkClass"], $fkInstanceArray ["fkTable"] );
				} );
			}
		}
		$this->addEditMemberFonctionality ( "dataElement" );
		return $dataElement;
	}

	/**
	 * Returns the captions for DataElement fields
	 * in display route
	 *
	 * @param array $captions
	 * @param string $className
	 */
	public function getElementCaptions($captions, $className, $instance) {
		return array_map ( "ucfirst", $captions );
	}

	/**
	 * Returns the dataTable responsible for displaying instances of the model
	 *
	 * @param array $instances objects to display
	 * @param string $model model class name (long name)
	 * @return DataTable
	 */
	public function getModelDataTable($instances, $model, $totalCount, $page = 1) {
		$adminRoute = $this->controller->_getBaseRoute ();
		$files = $this->controller->_getFiles ();
		$dataTable = $this->getDataTableInstance ( $instances, $model, $totalCount, $page );
		$attributes = $this->controller->_getAdminData ()->getFieldNames ( $model );
		$this->setDataTableAttributes ( $dataTable, $attributes, $model, $instances );
		$dataTable->setCaptions ( $this->getCaptions ( $attributes, $model ) );

		$dataTable->addClass ( "small very compact" );
		$lbl = new HtmlLabel ( "search-query", "<span id='search-query-content'></span>" );
		$icon = $lbl->addIcon ( "delete", false );
		$lbl->wrap ( "<span>", "</span>" );
		$lbl->setProperty ( "style", "display: none;" );
		$icon->getOnClick ( $adminRoute . $files->getRouteRefreshTable (), "#lv", [ "jqueryDone" => "replaceWith","hasLoader" => "internal" ] );

		$dataTable->addItemInToolbar ( $lbl );
		$dataTable->addSearchInToolbar ();
		$dataTable->setToolbarPosition ( PositionInTable::FOOTER );
		$dataTable->getToolbar ()->setSecondary ();
		return $dataTable;
	}

	public function setDataTableAttributes(DataTable $dataTable, $attributes, $model, $instances, $selector = null) {
		$modal = ($this->isModal ( $instances, $model ) ? "modal" : "no");

		$adminRoute = $this->controller->_getBaseRoute ();
		$files = $this->controller->_getFiles ();
		$dataTable->setButtons ( $this->getDataTableRowButtons () );
		$dataTable->setFields ( $attributes );
		if (array_search ( "password", $attributes ) !== false) {
			$dataTable->setValueFunction ( "password", function ($v) {
				return UString::mask ( $v );
			} );
		}
		$dataTable->setIdentifierFunction ( CRUDHelper::getIdentifierFunction ( $model ) );

		if (! isset ( $selector )) {
			if ($this->showDetailsOnDataTableClick ()) {
				$dataTable->getOnRow ( "click", $adminRoute . $files->getRouteDetails (), "#table-details", [ "selector" => $selector,"attr" => "data-ajax","hasLoader" => false,"jsCondition" => "!event.detail || event.detail==1","jsCallback" => "return false;" ] );
				$dataTable->setActiveRowSelector ( "active" );
			}

			$dataTable->setUrls ( [ "refresh" => $adminRoute . $files->getRouteRefresh (),"delete" => $adminRoute . $files->getRouteDelete (),"edit" => $adminRoute . $files->getRouteEdit () . "/" . $modal,"display" => $adminRoute . $files->getRouteDisplay () . "/" . $modal ] );
			$dataTable->setTargetSelector ( [ "delete" => "#table-messages","edit" => "#frm-add-update","display" => "#table-details" ] );
			$this->addEditMemberFonctionality ( "dataTable" );
		}
		$this->addAllButtons ( $dataTable, $attributes );
	}

	public function addEditMemberFonctionality($part) {
		if (($editMemberParams = $this->getEditMemberParams ()) !== false) {
			if (isset ( $editMemberParams [$part] )) {
				$params = $editMemberParams [$part];
				$params->compile ( $this->controller->_getBaseRoute (), $this->jquery, $part );
			}
		}
	}

	/**
	 *
	 * @param string $model The model class name (long name)
	 * @param number $totalCount The total count of objects
	 * @return void|number default : 6
	 */
	public function recordsPerPage($model, $totalCount = 0) {
		if ($totalCount > 6)
			return 6;
		return;
	}

	/**
	 * Returns the fields on which a grouping is performed
	 */
	public function getGroupByFields() {
		return;
	}

	/**
	 * Returns the dataTable instance for dispaying a list of object
	 *
	 * @param array $instances
	 * @param string $model
	 * @param number $totalCount
	 * @param number $page
	 * @return DataTable
	 */
	protected function getDataTableInstance($instances, $model, $totalCount, $page = 1): DataTable {
		$semantic = $this->jquery->semantic ();
		$recordsPerPage = $this->recordsPerPage ( $model, $totalCount );
		if (is_numeric ( $recordsPerPage )) {
			$grpByFields = $this->getGroupByFields ();
			if (is_array ( $grpByFields )) {
				$dataTable = $semantic->dataTable ( "lv", $model, $instances );
				$dataTable->setGroupByFields ( $grpByFields );
			} else {
				$dataTable = $semantic->jsonDataTable ( "lv", $model, $instances );
			}
			$dataTable->paginate ( $page, $totalCount, $recordsPerPage, 5 );
			$dataTable->onActiveRowChange ( '$("#table-details").html("");' );
			$dataTable->onSearchTerminate ( '$("#search-query-content").html(data);$("#search-query").show();$("#table-details").html("");' );
		} else {
			$dataTable = $semantic->dataTable ( "lv", $model, $instances );
		}
		return $dataTable;
	}

	/**
	 * Returns an array of buttons ["display","edit","delete"] to display for each row in dataTable
	 *
	 * @return string[]
	 */
	protected function getDataTableRowButtons() {
		return [ "edit","delete" ];
	}

	public function addAllButtons(DataTable $dataTable, $attributes) {
		$dataTable->onPreCompile ( function () use (&$dataTable) {
			$dataTable->getHtmlComponent ()->colRightFromRight ( 0 );
		} );
		$dataTable->addAllButtons ( false, [ "ajaxTransition" => "random" ], function ($bt) {
			$bt->addClass ( "circular" );
			$this->onDataTableRowButton ( $bt );
		}, function ($bt) {
			$bt->addClass ( "circular" );
			$this->onDataTableRowButton ( $bt );
		}, function ($bt) {
			$bt->addClass ( "circular" );
			$this->onDataTableRowButton ( $bt );
		} );
		$dataTable->setDisplayBehavior ( [ "jsCallback" => '$("#dataTable").hide();',"ajaxTransition" => "random" ] );
	}

	/**
	 * To override for modifying the dataTable row buttons
	 *
	 * @param HtmlButton $bt
	 */
	public function onDataTableRowButton(HtmlButton $bt) {
	}

	/**
	 * To override for modifying the showConfMessage dialog buttons
	 *
	 * @param HtmlButton $confirmBtn The confirmation button
	 * @param HtmlButton $cancelBtn The cancellation button
	 */
	public function onConfirmButtons(HtmlButton $confirmBtn, HtmlButton $cancelBtn) {
	}

	/**
	 * Returns the captions for list fields in showTable action
	 *
	 * @param array $captions
	 * @param string $className
	 */
	public function getCaptions($captions, $className) {
		return \array_map ( "ucfirst", $captions );
	}

	/**
	 * Returns the header for a single foreign object (element is an instance, issue from ManyToOne), (from DataTable)
	 *
	 * @param string $member
	 * @param string $className
	 * @param object $object
	 * @return HtmlHeader
	 */
	public function getFkHeaderElementDetails($member, $className, $object) {
		return new HtmlHeader ( "", 4, $member, "content" );
	}

	/**
	 * Returns the header for a list of foreign objects (issue from oneToMany or ManyToMany), (from DataTable)
	 *
	 * @param string $member
	 * @param string $className
	 * @param array $list
	 * @return HtmlHeader
	 */
	public function getFkHeaderListDetails($member, $className, $list) {
		return new HtmlHeader ( "", 4, $member . " (" . \count ( $list ) . ")", "content" );
	}

	/**
	 * Returns a component for displaying a single foreign object (manyToOne relation), (from DataTable)
	 *
	 * @param string $member
	 * @param string $className
	 * @param object $object
	 * @return \Ajax\common\html\BaseHtml
	 */
	public function getFkElementDetails($member, $className, $object) {
		return $this->jquery->semantic ()->htmlLabel ( "element-" . $className . "." . $member, $object . "" );
	}

	/**
	 * Returns a list component for displaying a collection of foreign objects (*ToMany relations), (from DataTable)
	 *
	 * @param string $member
	 * @param string $className
	 * @param array|\Traversable $list
	 * @return \Ajax\common\html\HtmlCollection
	 */
	public function getFkListDetails($member, $className, $list) {
		$element = $this->jquery->semantic ()->htmlList ( "list-" . $className . "." . $member );
		$element->setMaxVisible ( 15 );

		return $element->addClass ( "animated divided celled" );
	}

	/**
	 * Returns a component for displaying a foreign object (from DataTable)
	 *
	 * @param string $memberFK
	 * @param mixed $objectFK
	 * @param string $fkClass
	 * @param string $fkTable
	 * @return \Ajax\semantic\html\elements\HtmlHeader[]|\Ajax\common\html\BaseHtml[]|NULL
	 */
	public function getFkMemberElementDetails($memberFK, $objectFK, $fkClass, $fkTable) {
		$_fkClass = str_replace ( "\\", ".", $fkClass );
		$header = new HtmlHeader ( "", 4, $memberFK, "content" );
		if (is_array ( $objectFK ) || $objectFK instanceof \Traversable) {
			$header = $this->getFkHeaderListDetails ( $memberFK, $fkClass, $objectFK );
			$element = $this->getFkListDetails ( $memberFK, $fkClass, $objectFK );
			foreach ( $objectFK as $oItem ) {
				if (method_exists ( $oItem, "__toString" )) {
					$id = (CRUDHelper::getIdentifierFunction ( $fkClass )) ( 0, $oItem );
					$item = $element->addItem ( $oItem . "" );
					$item->setProperty ( "data-ajax", $_fkClass . "||" . $id );
					$item->addClass ( "showTable" );
					$this->onDisplayFkElementListDetails ( $item, $memberFK, $fkClass, $oItem );
				}
			}
		} else {
			if (method_exists ( $objectFK, "__toString" )) {
				$header = $this->getFkHeaderElementDetails ( $memberFK, $fkClass, $objectFK );
				$id = (CRUDHelper::getIdentifierFunction ( $fkClass )) ( 0, $objectFK );
				$element = $this->getFkElementDetails ( $memberFK, $fkClass, $objectFK );
				$element->setProperty ( "data-ajax", $_fkClass . "||" . $id )->addClass ( "showTable" );
			}
		}
		if (isset ( $element )) {
			return [ $header,$element ];
		}
		return null;
	}

	/**
	 * To modify for displaying an element in a list component of foreign objects (from DataTable)
	 *
	 * @param \Ajax\common\html\HtmlDoubleElement $element
	 * @param string $member
	 * @param string $className
	 * @param object $object
	 */
	public function onDisplayFkElementListDetails($element, $member, $className, $object) {
	}

	/**
	 * Returns a component for displaying a foreign object (from DataElement)
	 *
	 * @param string $memberFK
	 * @param mixed $objectFK
	 * @param string $fkClass
	 * @param string $fkTable
	 * @return string|NULL
	 */
	public function getFkMemberElement($memberFK, $objectFK, $fkClass, $fkTable) {
		$element = "";
		$_fkClass = str_replace ( "\\", ".", $fkClass );
		if (is_array ( $objectFK ) || $objectFK instanceof \Traversable) {
			$element = $this->getFkList ( $memberFK, $objectFK );
			foreach ( $objectFK as $oItem ) {
				if (method_exists ( $oItem, "__toString" )) {
					$id = (CRUDHelper::getIdentifierFunction ( $fkClass )) ( 0, $oItem );
					$item = $element->addItem ( $oItem . "" );
					$item->setProperty ( "data-ajax", $_fkClass . "||" . $id );
					$item->addClass ( "showTable" );
					$this->displayFkElementList ( $item, $memberFK, $fkClass, $oItem );
				}
			}
		} else {
			if (method_exists ( $objectFK, "__toString" )) {
				$id = (CRUDHelper::getIdentifierFunction ( $fkClass )) ( 0, $objectFK );
				$element = $this->getFkElement ( $memberFK, $fkClass, $objectFK );
				$element->setProperty ( "data-ajax", $_fkClass . "||" . $id )->addClass ( "showTable" );
			}
		}
		return $element;
	}

	/**
	 * Returns a list component for displaying a collection of foreign objects (*ToMany relations), (from DataElement)
	 *
	 * @param string $member
	 * @param string $className
	 * @param array|\Traversable $list
	 * @return \Ajax\common\html\HtmlCollection
	 */
	public function getFkList($member, $list) {
		$element = $this->jquery->semantic ()->htmlList ( "list-" . $member );
		$element->setMaxVisible ( 10 );
		return $element->addClass ( "animated" );
	}

	/**
	 * To modify for displaying an element in a list component of foreign objects, (from DataElement)
	 *
	 * @param \Ajax\common\html\HtmlDoubleElement $element
	 * @param string $member
	 * @param string $className
	 * @param object $object
	 */
	public function displayFkElementList($element, $member, $className, $object) {
	}

	/**
	 * Returns a component for displaying a single foreign object (manyToOne relation), (from DataElement)
	 *
	 * @param string $member
	 * @param string $className
	 * @param object $object
	 * @return \Ajax\common\html\BaseHtml
	 */
	public function getFkElement($member, $className, $object) {
		return $this->jquery->semantic ()->htmlLabel ( "element-" . $className . "." . $member, $object . "" );
	}

	/**
	 * To override to make sure that the detail of a clicked object is displayed or not
	 *
	 * @return boolean Return true if you want to see details
	 */
	public function showDetailsOnDataTableClick() {
		return true;
	}
}

