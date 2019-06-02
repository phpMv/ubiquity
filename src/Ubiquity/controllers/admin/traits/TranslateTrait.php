<?php

namespace Ubiquity\controllers\admin\traits;

use Ajax\semantic\components\validation\Rule;
use Ajax\semantic\html\base\constants\Direction;
use Ajax\semantic\html\collections\form\HtmlFormInput;
use Ajax\semantic\html\collections\form\HtmlFormTextarea;
use Ajax\semantic\html\elements\HtmlLabel;
use Ajax\semantic\widgets\datatable\PositionInTable;
use Ubiquity\controllers\admin\popo\TranslateMessage;
use Ubiquity\translation\MessagesCatalog;
use Ubiquity\translation\MessagesDomain;
use Ubiquity\translation\TranslatorManager;
use Ubiquity\utils\base\UArray;
use Ubiquity\utils\http\URequest;
use Ubiquity\utils\http\USession;
use Ubiquity\translation\MessagesUpdates;

/**
 *
 * @property \Ajax\php\ubiquity\JsUtils $jquery
 * @author jcheron <myaddressmail@gmail.com>
 *
 */
trait TranslateTrait {
	protected $newTransRowId;

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
	 * @param array $messages
	 * @param string $locale
	 * @param string $domain
	 * @param string $localeCompare
	 * @return \Ajax\semantic\widgets\datatable\DataTable
	 */
	private function getDtDomain($messages, $locale, $domain, $localeCompare = null) {
		$baseRoute = $this->_getFiles ()->getAdminBaseRoute ();

		if (isset ( $localeCompare )) {
			$msgDomainCompare = new MessagesDomain ( $localeCompare, TranslatorManager::getLoader (), $domain );
			$msgDomainCompare->load ();
			$messages = TranslateMessage::loadAndCompare ( $messages, $msgDomainCompare->getMessages () );
		} else {
			$messages = TranslateMessage::load ( $messages );
		}
		$dt = $this->jquery->semantic ()->dataTable ( 'dtDomain-' . $locale . '-' . $domain, TranslateMessage::class, $messages );
		$dtId = $dt->getIdentifier ();
		$dt->setFields ( [ 'mkey','mvalue' ] );
		$this->newTransRowId = uniqid ( 'key' );
		if (isset ( $localeCompare )) {
			$dt->setValueFunction ( 'mvalue', function ($value, $instance) {
				$txt = new HtmlFormTextarea ( '', '', $value );
				$txt->wrap ( new HtmlLabel ( '', $instance->getCompare () ) );
				$txt->setRows ( 1 );
				$this->addDatasAttr ( $txt, $instance->getMkey (), $value, 'value', $instance->getNewKey () );
				return $txt;
			} );
			$dt->setValueFunction ( 'mkey', function ($key, $instance) use ($localeCompare) {
				$txt = new HtmlFormInput ( '', null, 'text', $key );
				$txt->wrap ( new HtmlLabel ( '', $localeCompare ) );
				$this->addDatasAttr ( $txt, $key, $instance->getMvalue (), 'key', $instance->getNewKey () );
				return $txt;
			} );
		} else {
			$dt->setValueFunction ( 'mvalue', function ($value, $instance) {
				$txt = new HtmlFormTextarea ( '', null, $value );
				$this->addDatasAttr ( $txt, $instance->getMkey (), $value, 'value', $instance->getNewKey () );
				$txt->setRows ( 1 );
				return $txt;
			} );
			$dt->setValueFunction ( 'mkey', function ($key, $instance) {
				$txt = new HtmlFormInput ( '', null, 'text', $key );
				$this->addDatasAttr ( $txt, $key, $instance->getMvalue (), 'key', $instance->getNewKey () );
				return $txt;
			} );
		}

		$dt->addDeleteButton ();
		$dt->setEdition ( true );
		$dt->setUrls ( [ 'refresh' => $baseRoute . '/refreshDomain/' . $locale . '/' . $domain ] );
		$dt->addClass ( 'selectable' );
		$dt->setLibraryId ( 'dtDomain' );
		$lbl = new HtmlLabel ( "search-query-" . $locale . $domain, "<span id='search-query-content-" . $locale . $domain . "'></span>" );
		$icon = $lbl->addIcon ( "delete", false );
		$lbl->wrap ( "<span>", "</span>" );
		$lbl->setProperty ( "style", "display: none;" );
		$icon->getOnClick ( $baseRoute . '/refreshDomainAll/' . $locale . '/' . $domain, '#' . $dtId, [ "jqueryDone" => "replaceWith","hasLoader" => "internal" ] );
		$this->jquery->change ( '#' . $dtId . ' tbody textarea,#' . $dtId . ' input', '$("#domain-name-' . $locale . $domain . '").html($("#domain-name-' . $locale . $domain . '").attr("data-value")+"*");' );
		$this->jquery->postOn ( 'change', '#' . $dtId . ' tbody textarea,#' . $dtId . ' tbody input', $baseRoute . '/updateTranslation/' . $locale . '/' . $domain, '{n:$(this).closest("tr").find("input").first().attr("data-new") || 0,v:$(this).closest("tr").find("textarea").first().val(),k:$(this).closest("tr").find("input").first().val()}', '#update-' . $locale . $domain, [
																																																																																															'hasLoader' => false,
																																																																																															'attr' => 'data-key' ] );
		$this->jquery->postOnClick ( '#' . $dtId . ' ._delete', $baseRoute . '/deleteTranslation/' . $locale . '/' . $domain, '{n:$(this).closest("tr").find("input").first().attr("data-new") || 0,k:$(this).closest("tr").find("input").first().attr("data-key")}', '#update-' . $locale . $domain, [ 'hasLoader' => false ] );
		$this->jquery->execAtLast ( '$("._ddAddMessages").dropdown();' );
		$dt->addItemInToolbar ( $lbl );
		$dt->addSearchInToolbar ();
		$dt->setToolbarPosition ( PositionInTable::FOOTER );
		$dt->getToolbar ()->setSecondary ();
		return $dt;
	}

	private function addDatasAttr($elm, $key, $value, $type = 'value', $newKey = null) {
		if ($key == '') {
			$key = '_new_';
			$value = $this->newTransRowId;
			$elm->getDataField ()->setProperty ( 'data-new', true );
		}
		if (isset ( $newKey )) {
			$key = '_new_';
			$value = $newKey;
		}
		$elm->getDataField ()->setProperty ( 'data-key', $type . '|' . $key . '|' . $value );
	}

	private function getMessagesDomain($locale, $domain, $display = true) {
		$msgDomain = new MessagesDomain ( $locale, TranslatorManager::getLoader (), $domain );
		$msgDomain->load ();
		$messages = $msgDomain->getMessages ();
		$messagesUpdates = new MessagesUpdates ( $locale, $domain );
		if ($messagesUpdates->exists ()) {
			$messagesUpdates->load ();
			if ($messagesUpdates->hasUpdates () && $display) {
				$messages = $messagesUpdates->mergeMessages ( $messages );
				$this->displayTranslationUpdates ( $messagesUpdates, $locale, $domain );
			}
		}
		return $messages;
	}

	private function _loadDomain($locale, $domain) {
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
		$messages = $this->getMessagesDomain ( $locale, $domain );
		$dt = $this->getDtDomain ( $messages, $locale, $domain );
		$dt->asForm ();
		$dt->autoPaginate ( 1, 50, 9 );
		$dtId = '#' . $dt->getIdentifier ();
		$this->jquery->postOnClick ( '#compare-to-' . $locale, $baseRoute . '/compareToLocale/' . $domain . '/' . $locale, '{p: $("' . $dtId . ' .item.active").first().attr("data-page"),ol: $("#input-' . $dd->getIdentifier () . '").val()}', $dtId . ' tbody', [ 'jqueryDone' => 'replaceWith','hasLoader' => 'internal' ] );

		$this->jquery->getOnClick ( '._addMessages', $baseRoute . '/frmMultipleMessages/' . $locale . '/' . $domain, '#form-' . $locale . $domain, [ 'hasLoader' => 'internal' ] );

		$this->jquery->exec ( '$("#locale-' . $locale . '").hide();', true );
		$this->jquery->click ( '#return-' . $locale, '$("#locale-' . $locale . '").show();$("#domain-' . $locale . '").html("");' );
		return $dt;
	}

	private function displayTranslationUpdates(MessagesUpdates $messagesUpdates, $locale, $domain) {
		$baseRoute = $this->_getFiles ()->getAdminBaseRoute ();
		$bt = $this->jquery->semantic ()->htmlButton ( 'bt-save', 'Save' );
		$bt->addIcon ( 'save' );
		$bt->addLabel ( $messagesUpdates, true )->setPointing ( 'right' );
		$bt->getContent () [1]->addClass ( 'green' )->getOnClick ( $baseRoute . '/saveTranslationsUpdates/' . $locale . '/' . $domain, '#domain-' . $locale, [ 'hasLoader' => 'internal' ] );
		$btDelete = $this->jquery->semantic ()->htmlButton ( 'bt-cancel-updates', 'Cancel updates', 'red' );
		$btDelete->addIcon ( 'remove' );
		$btDelete->getOnClick ( $baseRoute . '/cancelTranslationsUpdates/' . $locale . '/' . $domain, '#domain-' . $locale, [ 'hasLoader' => 'internal' ] );
		return $bt . $btDelete;
	}

	public function loadDomain($locale, $domain) {
		$this->_loadDomain ( $locale, $domain );
		$this->jquery->renderView ( '@framework/Admin/translate/domain.html', [ 'locale' => $locale,'domain' => $domain ] );
	}

	public function refreshDomainAll($locale, $domain) {
		$dt = $this->_loadDomain ( $locale, $domain, false );
		$dt->setLibraryId ( "_compo_" );
		$this->jquery->renderView ( "@framework/main/component.html" );
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
		$messages = $this->getMessagesDomain ( $locale, $domain );
		if (isset ( $_POST ['s'] )) {
			$rep = [ ];
			$s = $_POST ['s'];
			foreach ( $messages as $k => $v ) {
				if (strpos ( $k, $s ) !== false || strpos ( $v, $s ) !== false) {
					$rep [$k] = $v;
				}
			}
			$messages = $rep;
			$this->jquery->execAtLast ( '$("#search-query-content-' . $locale . $domain . '").html("' . $_POST ["s"] . '");$("#search-query-' . $locale . $domain . '").show();' );
		}
		$dt = $this->getDtDomain ( $messages, $locale, $domain, $otherLocale );
		$p = URequest::post ( 'p', 1 );
		$dt->autoPaginate ( is_numeric ( $p ) ? $p : 1, 50, 9 );
		$dt->refresh ();
		$dt->setLibraryId ( "_compo_" );
		$this->jquery->renderView ( "@framework/main/component.html" );
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

	public function updateTranslation($locale, $domain, $key) {
		list ( $type, $oldKey, $oldValue ) = explode ( '|', $key );
		$newKey = $_POST ['k'];
		$newValue = $_POST ['v'];
		$new = URequest::getBoolean ( 'n' );
		$messagesUpdates = new MessagesUpdates ( $locale, $domain );
		$messagesUpdates->load ();
		if ($type == 'key') {
			if ($oldKey === '_new_') {
				$messagesUpdates->addValue ( $newKey, $newValue, $oldValue );
			} elseif ($oldKey !== $newKey) {
				$messagesUpdates->replaceKey ( $oldKey, $newKey, $newValue );
			}
		} elseif ($type == 'value') {
			if ($oldValue !== $newValue) {
				if ($oldKey !== '_new_') {
					$messagesUpdates->updateValue ( $newKey, $newValue );
				} elseif ($newKey != null) {
					$messagesUpdates->addValue ( $newKey, $newValue );
				}
			}
		}
		$messagesUpdates->save ();
		if ($messagesUpdates->hasUpdates ()) {
			echo $this->displayTranslationUpdates ( $messagesUpdates, $locale, $domain );
			if ($new) {
				$this->jquery->exec ( "
									var selector='#dtDomain-" . $locale . "-" . $domain . " tbody tr';
									var clone=\$(selector).last().clone(true);
									var uuid=\$.create_UUID();\$('[data-new]').removeAttr('data-new');
									var input=clone.find('input').first();var textarea=clone.find('textarea').first();
									input.attr('data-key','key|_new_|key'+uuid);textarea.attr('data-key','value|_new_|key'+uuid);
									input.val('');textarea.val('');
									\$('[data-new]').removeAttr('data-new');
									\$(selector).last().after(clone);", true );
			}
			echo $this->jquery->compile ( $this->view );
		}
	}

	public function deleteTranslation($locale, $domain) {
		$new = URequest::getBoolean ( 'n' );
		$key = $_POST ['k'];
		$messagesUpdates = new MessagesUpdates ( $locale, $domain );
		$messagesUpdates->load ();
		if (! $new) {
			list ( $type, $oldKey, $oldValue ) = explode ( '|', $key );

			if ($oldKey === '_new_') {
				$messagesUpdates->removeNewKey ( $oldValue );
			} else {
				$messagesUpdates->addToDelete ( $oldKey );
			}
			$messagesUpdates->save ();
			$this->jquery->execAtLast ( '$("[data-key=\'' . $key . '\']").closest("tr").remove();' );
		}
		if ($messagesUpdates->hasUpdates ()) {
			echo $this->displayTranslationUpdates ( $messagesUpdates, $locale, $domain );
		}
		echo $this->jquery->compile ();
	}

	public function saveTranslationsUpdates($locale, $domain) {
		TranslatorManager::start ();
		$msgDomain = new MessagesDomain ( $locale, TranslatorManager::getLoader (), $domain );
		$msgDomain->load ();
		$messages = $msgDomain->getMessages ();
		$messagesUpdates = new MessagesUpdates ( $locale, $domain );
		if ($messagesUpdates->exists ()) {
			$messagesUpdates->load ();
			$messages = $messagesUpdates->mergeMessages ( $messages, true );
			$msgDomain->setMessages ( $messages );
			try {
				$msgDomain->store ();
				$messagesUpdates->delete ();
			} catch ( \Exception $e ) {
			}
		}
		$this->loadDomain ( $locale, $domain );
	}

	public function cancelTranslationsUpdates($locale, $domain) {
		$messagesUpdates = new MessagesUpdates ( $locale, $domain );
		if ($messagesUpdates->exists ()) {
			$messagesUpdates->delete ();
		}
		$this->loadDomain ( $locale, $domain );
	}

	public function frmMultipleMessages($locale, $domain) {
		$this->jquery->execOn ( "click", "#cancel-multiple-messages-" . $locale . $domain, '$("#form-' . $locale . $domain . '").html("");' );
		$baseRoute = $this->_getFiles ()->getAdminBaseRoute ();
		$this->jquery->postFormOnClick ( "#validate-multiple-messages-" . $locale . $domain, $baseRoute . '/addMultipleMessages/' . $locale . '/' . $domain, 'frm-multiple-' . $locale . $domain, '#domain-' . $locale, [ 'hasLoader' => 'internal' ] );
		$this->jquery->renderView ( '@framework/Admin/translate/frmMultipleMessages.html', [ 'locale' => $locale,'domain' => $domain ] );
	}

	public function addMultipleMessages($locale, $domain) {
		$messagesUpdates = new MessagesUpdates ( $locale, $domain );
		$messagesUpdates->load ();
		$sep = $_POST ["separator"] ?? '=';
		$inlineMessages = $_POST ['messages'];
		$splitMessages = explode ( PHP_EOL, $inlineMessages );
		foreach ( $splitMessages as $msg ) {
			$kv = explode ( $sep, $msg );
			if (isset ( $kv [0] ) && $kv [0] != null) {
				$messagesUpdates->addValue ( $kv [0], $kv [1] ?? '', uniqid ( 'key' ) );
			}
		}
		$messagesUpdates->save ();
		$this->loadDomain ( $locale, $domain );
	}
}

