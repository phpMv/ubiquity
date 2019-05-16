<?php

namespace Ubiquity\controllers\admin\traits;

use Ubiquity\translation\MessagesCatalog;
use Ubiquity\translation\TranslatorManager;
use Ubiquity\translation\MessagesDomain;
use Ajax\semantic\html\elements\HtmlLabel;
/**
 * @property \Ajax\php\ubiquity\JsUtils $jquery
 * @author jcheron <myaddressmail@gmail.com>
 *
 */
trait TranslateTrait {
	public function loadLocale($locale){
		$messagesCatalog=new MessagesCatalog($locale, TranslatorManager::getLoader());
		$messagesCatalog->load();
		$msgDomains=$messagesCatalog->getMessagesDomains();
		$dt=$this->jquery->semantic()->dataTable('dt-'.$locale, MessagesDomain::class,$msgDomains);
		$dt->setFields(['locale','domain','messages']);
		$dt->setValueFunction('messages', function($value){
			$nb=0;
			if(is_array($value)){
				$nb=count($value);
			}
			return new HtmlLabel('',$nb,'mail');
		});
		$dt->setIdentifierFunction('getDomain');
		$dt->addEditDeleteButtons(true,[],function($bt) use ($locale){
			$bt->addClass($locale);
		});
		$dt->setActiveRowSelector();

		$this->jquery->getOnClick('._edit.'.$locale, "/Admin/loadDomain/".$locale."/",'#domain',['attr'=>'data-ajax']);
		return $this->loadView('@framework/Admin/translate/locale.html',['locale'=>$locale,'dt'=>$dt],true);
	}
	
	public function loadDomain($locale,$domain){
		TranslatorManager::start();
		$msgDomain=new MessagesDomain($locale,TranslatorManager::getLoader(),$domain);
		$msgDomain->load();
		$messages=$msgDomain->getMessages();
		$this->loadView('@framework/Admin/translate/domain.html',['messages'=>$messages]);
	}
}

