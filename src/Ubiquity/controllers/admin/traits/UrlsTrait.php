<?php

namespace Ubiquity\controllers\admin\traits;

/**
 * Contains all methods returning the urls for CRUDControllers
 * @author jc
 *
 */
trait UrlsTrait {
	
	/**
	 * To override
	 * Returns the route for refreshing the index route (default : /refresh_)
	 * @return string
	 */
	public function getRouteRefresh(){
		return "/refresh_";
	}

	/**
	 * To override
	 * Returns the route for the detail route, when the user click on a dataTable row (default : /showDetail)
	 * @return string
	 */
	public function getRouteDetails(){
		return "/showDetail";
	}
	
	/**
	 * To override
	 * Returns the route for deleting an instance (default : /delete)
	 * @return string
	 */
	public function getRouteDelete(){
		return "/delete";
	}
	
	/**
	 * To override
	 * Returns the route for editing an instance (default : /edit)
	 * @return string
	 */
	public function getRouteEdit(){
		return "/edit";
	}
	
	/**
	 * To override
	 * Returns the route for displaying an instance (default : /display)
	 * @return string
	 */
	public function getRouteDisplay(){
		return "/display";
	}
	
	/**
	 * To override
	 * Returns the route for refreshing the dataTable (default : /refreshTable)
	 * @return string
	 */
	public function getRouteRefreshTable(){
		return "/refreshTable";
	}
	
	/**
	 * To override
	 * Returns the url associated with a foreign key instance in list
	 * @param string $model
	 * @return string
	 */
	public function getDetailClickURL($model){
		return "";
	}
}

