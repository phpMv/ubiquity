<?php

namespace Ubiquity\controllers\admin\traits;

use Ajax\semantic\components\validation\Rule;
use Ajax\semantic\html\base\constants\Direction;
use Ajax\semantic\html\collections\form\HtmlFormInput;
use Ajax\semantic\html\collections\form\HtmlFormTextarea;
use Ajax\semantic\html\elements\HtmlLabel;
use Ubiquity\controllers\admin\popo\TranslateMessage;
use Ubiquity\translation\MessagesCatalog;
use Ubiquity\translation\MessagesDomain;
use Ubiquity\translation\TranslatorManager;
use Ubiquity\utils\base\UArray;
use Ubiquity\utils\http\URequest;
use Ubiquity\utils\http\USession;

/**
 *
 * @property \Ajax\php\ubiquity\JsUtils $jquery
 * @author jcheron <myaddressmail@gmail.com>
 *
 */
trait TranslateTrait {

	protected function _translate($loc, $baseRoute) {
		TranslatorManager::start ();
		$locales = TranslatorManager::getLocales ();
		if (sizeof ( $locales ) == 0) {
			$locales = TranslatorManager::initialize ();
		}
		$tabs = $this->jquery->semantic ()->htmlTab ( "locales" );
		foreach ( $locales as $locale ) {
			$tabs->addTab ( $locale, $this->loadLocale ( $locale ) );
		}
		$tabs->activate ( array_search ( $loc, $locales ) );

		$frm = $this->jquery->semantic ()->htmlForm ( "frmLocale" );
		$frm->setValidationParams ( [ "on" => "blur","inline" => true ] );
		$fields = $frm->addFields ();
		$input = $fields->addInput ( "localeName", null, "text", "", "Locale name" )->addRules ( [ [ "empty","Locale name must have a value" ],"regExp[/^[A-Za-z]\w*$/]",[ "checkLocale","Locale {value} is not a valid name!" ] ] )->setWidth ( 8 );
		$input->addAction ( "Add locale", true, "plus", true )->addClass ( "teal" )->asSubmit ();
		$frm->setSubmitParams ( $baseRoute . '/createLocale', '#translations-refresh', [ 'jqueryDone' => 'replaceWith','hasLoader' => 'internal' ] );

		$this->jquery->exec ( Rule::ajax ( $this->jquery, "checkLocale", $this->_getFiles ()->getAdminBaseRoute () . "/_checkLocale", "{}", "result=data.result;", "postForm", [ "form" => "frmLocale" ] ), true );
		$this->jquery->renderView ( $this->_getFiles ()->getViewTranslateIndex () );
	}

	public function loadLocale($locale) {
		$baseRoute = $this->_getFiles ()->getAdminBaseRoute ();

		$messagesCatalog = new MessagesCatalog ( $locale, TranslatorManager::getLoader () );
		$messagesCatalog->load ();
		$msgDomains = $messagesCatalog->getMessagesDomains ();

		$frm = $this->jquery->semantic ()->htmlForm ( "frmDomain-" . $locale );
		$frm->setValidationParams ( [ "on" => "blur","inline" => true ] );
		$fields = $frm->addFields ();
		$input = $fields->addInput ( "name-" . $locale, null, "text", "", "Domain name" )->addRules ( [ [ "empty","Domain name must have a value" ],"regExp[/^[A-Za-z]\w*$/]" ] )->setWidth ( 8 );
		$input->setName ( 'domainName' );
		$ck = $input->labeledCheckbox ( Direction::LEFT, "Add in all locales", "all-locales" );
		$ck->getField ()->setProperty ( 'name', 'ck-all-locales' );
		$input->addAction ( "Add domain", true, "plus", true )->addClass ( "teal" )->asSubmit ();
		$frm->setSubmitParams ( $baseRoute . "/addDomain/" . $locale, "#translations-refresh" );

		$dt = $this->jquery->semantic ()->dataTable ( 'dt-' . $locale, MessagesDomain::class, $msgDomains );
		$dt->setFields ( [ 'domain','messages' ] );
		$dt->setValueFunction ( 'messages', function ($value) {
			$nb = 0;
			if (is_array ( $value )) {
				$nb = count ( $value );
			}
			return new HtmlLabel ( '', $nb, 'mail' );
		} );
		$dt->setIdentifierFunction ( 'getDomain' );
		$dt->addEditButton ( true, [ ], function ($bt) use ($locale) {
			$bt->addClass ( $locale );
		} );
		$dt->setActiveRowSelector ();

		$this->jquery->getOnClick ( '._edit.' . $locale, "/Admin/loadDomain/" . $locale . "/", '#domain-' . $locale, [ 'attr' => 'data-ajax','hasLoader' => 'internal' ] );
		return $this->loadView ( '@framework/Admin/translate/locale.html', [ 'locale' => $locale,'dt' => $dt,'frm' => $frm ], true );
	}

	/**
	 *
	 * @param string $locale
	 * @param string $domain
	 * @param string $localeCompare
	 * @return \Ajax\semantic\widgets\datatable\DataTable
	 */
	private function getDtDomain($locale, $domain, $localeCompare = null) {
		$baseRoute = $this->_getFiles ()->getAdminBaseRoute ();
		$msgDomain = new MessagesDomain ( $locale, TranslatorManager::getLoader (), $domain );
		$msgDomain->load ();
		$messages = $msgDomain->getMessages ();
		if (isset ( $localeCompare )) {
			$msgDomainCompare = new MessagesDomain ( $localeCompare, TranslatorManager::getLoader (), $domain );
			$msgDomainCompare->load ();
			$messages = TranslateMessage::loadAndCompare ( $messages, $msgDomainCompare->getMessages () );
		} else {
			$messages = TranslateMessage::load ( $messages );
		}
		$dt = $this->jquery->semantic ()->dataTable ( 'dtDomain-' . $locale . '-' . $domain, TranslateMessage::class, $messages );
		$dt->setFields ( [ 'mkey','mvalue' ] );
		if (isset ( $localeCompare )) {
			$dt->setValueFunction ( 'mvalue', function ($value, $instance) {
				$txt = new HtmlFormTextarea ( '', '', $value );
				$txt->wrap ( new HtmlLabel ( '', $instance->getCompare () ) );
				$txt->setRows ( 1 );
				return $txt;
			} );
			$dt->setValueFunction ( 'mkey', function ($value) use ($localeCompare) {
				$txt = new HtmlFormInput ( '', null, 'text', $value );
				$txt->wrap ( new HtmlLabel ( '', $localeCompare ) );
				return $txt;
			} );
		} else {
			$dt->setValueFunction ( 'mvalue', function ($value) {
				$txt = new HtmlFormTextarea ( '', null, $value );
				$txt->setRows ( 1 );
				return $txt;
			} );
			$dt->fieldAsInput ( 'mkey' );
		}

		$dt->addDeleteButton ();
		$dt->setEdition ( true );
		$dt->setUrls ( [ 'refresh' => $baseRoute . '/refreshDomain/' . $locale . '/' . $domain ] );
		$dt->addClass ( 'selectable' );
		$dt->setLibraryId ( 'dtDomain' );
		return $dt;
	}

	public function loadDomain($locale, $domain) {
		USession::delete ( 'ol' );
		$baseRoute = $this->_getFiles ()->getAdminBaseRoute ();
		TranslatorManager::start ();
		$locales = TranslatorManager::getLocales ();
		$locales = UArray::removeOne ( $locales, $locale );
		$dd = $this->jquery->semantic ()->htmlDropdown ( 'dd-locales-' . $locale, 'Compare to...', array_combine ( $locales, $locales ) );
		$dd->addInput ( 'compareTo' );
		$dd->asButton ();
		$dd->addClass ( 'basic' );
		$dd->setLibraryId ( 'dd-locales' );
		$dt = $this->getDtDomain ( $locale, $domain );
		$dt->asForm ();
		$dt->autoPaginate ( 1, 10 );
		$dtId = '#' . $dt->getIdentifier ();
		$this->jquery->postOnClick ( '#compare-to-' . $locale, $baseRoute . '/compareToLocale/' . $domain . '/' . $locale, '{p: $("' . $dtId . ' .item.active").first().attr("data-page"),ol: $("#input-dd-locales-' . $locale . '").val()}', $dtId . ' tbody', [ 'jqueryDone' => 'replaceWith','hasLoader' => 'internal' ] );
		$this->jquery->exec ( '$("#locale-' . $locale . '").hide();', true );
		$this->jquery->click ( '#return-' . $locale, '$("#locale-' . $locale . '").show();$("#domain-' . $locale . '").html("");' );
		$this->jquery->renderView ( '@framework/Admin/translate/domain.html', [ 'locale' => $locale ] );
	}

	public function compareToLocale($domain, $locale) {
		$ol = URequest::post ( 'ol' );
		USession::set ( 'ol', $ol );
		$this->refreshDomain ( $locale, $domain );
	}

	public function refreshDomain($locale, $domain, $otherLocale = null) {
		TranslatorManager::start ();
		if (USession::exists ( 'ol' )) {
			$otherLocale = USession::get ( 'ol' );
		}
		$dt = $this->getDtDomain ( $locale, $domain, $otherLocale );
		$p = URequest::post ( 'p', 1 );
		$dt->autoPaginate ( is_numeric ( $p ) ? $p : 1, 10 );
		echo $dt->refresh ();
		// $this->jquery->renderView('@framework/Admin/translate/domain.html');
	}

	public function createLocale() {
		if (URequest::isPost ()) {
			$baseRoute = $this->_getFiles ()->getAdminBaseRoute ();
			if (isset ( $_POST ["localeName"] ) && $_POST ["localeName"] != null) {
				$loc = $_POST ["localeName"];
				TranslatorManager::createLocale ( $loc );
			} else {
				$loc = URequest::getDefaultLanguage ();
			}
			$this->_translate ( $loc, $baseRoute );
		}
	}

	public function _checkLocale() {
		if (URequest::isPost ()) {
			TranslatorManager::start ();
			$result = [ ];
			header ( 'Content-type: application/json' );
			if (isset ( $_POST ["localeName"] ) && $_POST ["localeName"] != null) {
				$localeName = $_POST ["localeName"];
				$locales = TranslatorManager::getLocales ();
				$result = TranslatorManager::isValidLocale ( $localeName ) && (array_search ( $localeName, $locales ) === false);
			} else {
				$result = true;
			}
			echo json_encode ( [ 'result' => $result ] );
		}
	}

	public function addDomain($locale) {
		if (URequest::isPost ()) {
			TranslatorManager::start ();
			if (isset ( $_POST ["domainName"] ) && $_POST ["domainName"] != null) {
				$domainName = $_POST ["domainName"];
				if (isset ( $_POST ["ck-all-locales"] )) {
					$locales = TranslatorManager::getLocales ();
					foreach ( $locales as $loc ) {
						TranslatorManager::createDomain ( $loc, $domainName, [ 'newKey' => 'New key for translations' ] );
					}
				} else {
					TranslatorManager::createDomain ( $locale, $domainName, [ 'newKey' => 'New key for translations' ] );
				}
			}
			$this->_translate ( $locale, $this->_getFiles ()->getAdminBaseRoute () );
		}
	}
}

