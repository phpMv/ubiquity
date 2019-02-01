<?php
%namespace%

use Ubiquity\controllers\seo\SeoController;

 /**
 * SEO Controller %controllerName%
 **/
class %controllerName% extends SeoController {

	public function __construct(){
		parent::__construct();
		$this->urlsKey="%urlsFile%";
		$this->seoTemplateFilename="%sitemapTemplate%";
	}
	
	 /**
	 * %route%
	 **/
	public function index(){
		return parent::index();
	}
}
