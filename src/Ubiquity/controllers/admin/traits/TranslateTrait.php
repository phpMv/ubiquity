<?php
namespace Ubiquity\controllers\admin\traits;

use Ajax\semantic\components\validation\Rule;
use Ajax\semantic\html\elements\HtmlLabel;
use Ubiquity\translation\MessagesCatalog;
use Ubiquity\translation\MessagesDomain;
use Ubiquity\translation\TranslatorManager;
use Ubiquity\utils\http\URequest;

/**
 *
 * @property \Ajax\php\ubiquity\JsUtils $jquery
 * @author jcheron <myaddressmail@gmail.com>
 *        
 */
trait TranslateTrait {

	protected function _translate($loc, $baseRoute) {
		TranslatorManager::start();
		$locales = TranslatorManager::getLocales();
		if (sizeof($locales) == 0) {
			$locales = TranslatorManager::initialize();
		}
		$tabs = $this->jquery->semantic()->htmlTab("locales");
		foreach ($locales as $locale) {
			$tabs->addTab($locale, $this->loadLocale($locale));
		}
		$tabs->activate(array_search($loc, $locales));

		$frm = $this->jquery->semantic()->htmlForm("frmLocale");
		$frm->setValidationParams([
			"on" => "blur",
			"inline" => true
		]);
		$fields = $frm->addFields();
		$input = $fields->addInput("localeName", null, "text", "", "Locale name")
			->addRules([
			[
				"empty",
				"Locale name must have a value"
			],
			"regExp[/^[A-Za-z]\w*$/]",
			[
				"checkLocale",
				"Locale {value} is not a valid name!"
			]
		])
			->setWidth(8);
		$input->addAction("Add locale", true, "plus", true)
			->addClass("teal")
			->asSubmit();
		$frm->setSubmitParams($baseRoute . '/createLocale', '#translations-refresh', [
			'jqueryDone' => 'replaceWith',
			'hasLoader' => 'internal'
		]);

		$this->jquery->exec(Rule::ajax($this->jquery, "checkLocale", $this->_getFiles()
			->getAdminBaseRoute() . "/_checkLocale", "{}", "result=data.result;", "postForm", [
			"form" => "frmLocale"
		]), true);
		$this->jquery->renderView($this->_getFiles()
			->getViewTranslateIndex());
	}

	public function loadLocale($locale) {
		$messagesCatalog = new MessagesCatalog($locale, TranslatorManager::getLoader());
		$messagesCatalog->load();
		$msgDomains = $messagesCatalog->getMessagesDomains();
		$dt = $this->jquery->semantic()->dataTable('dt-' . $locale, MessagesDomain::class, $msgDomains);
		$dt->setFields([
			'locale',
			'domain',
			'messages'
		]);
		$dt->setValueFunction('messages', function ($value) {
			$nb = 0;
			if (is_array($value)) {
				$nb = count($value);
			}
			return new HtmlLabel('', $nb, 'mail');
		});
		$dt->setIdentifierFunction('getDomain');
		$dt->addEditDeleteButtons(true, [], function ($bt) use ($locale) {
			$bt->addClass($locale);
		});
		$dt->setActiveRowSelector();

		$this->jquery->getOnClick('._edit.' . $locale, "/Admin/loadDomain/" . $locale . "/", '#domain', [
			'attr' => 'data-ajax'
		]);
		return $this->loadView('@framework/Admin/translate/locale.html', [
			'locale' => $locale,
			'dt' => $dt
		], true);
	}

	public function loadDomain($locale, $domain) {
		TranslatorManager::start();
		$msgDomain = new MessagesDomain($locale, TranslatorManager::getLoader(), $domain);
		$msgDomain->load();
		$messages = $msgDomain->getMessages();
		$this->loadView('@framework/Admin/translate/domain.html', [
			'messages' => $messages
		]);
	}

	public function createLocale() {
		if (URequest::isPost()) {
			$baseRoute = $this->_getFiles()->getAdminBaseRoute();
			if (isset($_POST["localeName"]) && $_POST["localeName"] != null) {
				$loc = $_POST["localeName"];
				TranslatorManager::createLocale($loc);
			} else {
				$loc = URequest::getDefaultLanguage();
			}
			$this->_translate($loc, $baseRoute);
		}
	}

	public function _checkLocale() {
		if (URequest::isPost()) {
			TranslatorManager::start();
			$result = [];
			header('Content-type: application/json');
			if (isset($_POST["localeName"]) && $_POST["localeName"] != null) {
				$localeName = $_POST["localeName"];
				$locales = TranslatorManager::getLocales();
				$result = TranslatorManager::isValidLocale($localeName) && (array_search($localeName, $locales) === false);
			} else {
				$result = true;
			}
			echo json_encode([
				'result' => $result
			]);
		}
	}
}

