<?php
namespace Ubiquity\controllers\admin\traits;

use Ajax\semantic\html\collections\HtmlMessage;
use Ubiquity\controllers\Startup;
use Ubiquity\utils\base\UArray;
use Ubiquity\utils\http\URequest;
use Ubiquity\utils\http\UResponse;
use Ubiquity\utils\base\UString;
use Ubiquity\db\Database;
use Ubiquity\utils\base\CodeUtils;

/**
 *
 * @author jc
 * @property \Ajax\JsUtils $jquery
 * @property \Ubiquity\views\View $view
 */
trait ConfigTrait {

	abstract public function _getAdminData();

	abstract public function _getAdminViewer();

	abstract public function _getFiles();

	abstract public function loadView($viewName, $pData = NULL, $asString = false);

	abstract public function config($hasHeader = true);

	abstract public function models($hasHeader = true);

	abstract protected function showConfMessage($content, $type, $title, $icon, $url, $responseElement, $data, $attributes = NULL): HtmlMessage;

	abstract protected function showSimpleMessage($content, $type, $title = null, $icon = "info", $timeout = NULL, $staticName = null): HtmlMessage;

	public function formConfig($hasHeader = true) {
		global $config;
		if ($hasHeader === true) {
			$this->getHeader("config");
		}
		$this->_getAdminViewer()->getConfigDataForm($config, $hasHeader);
		$this->jquery->compile($this->view);
		$this->loadView($this->_getFiles()
			->getViewConfigForm());
	}

	public function _config() {
		$config = Startup::getConfig();
		echo $this->_getAdminViewer()->getConfigDataElement($config);
		echo $this->jquery->compile($this->view);
	}

	public function submitConfig($partial = true) {
		$result = Startup::getConfig();
		$postValues = $_POST;
		if ($partial !== true) {
			if (isset($postValues["lbl-ck-div-de-database-input-cache"])) {
				unset($postValues["lbl-ck-div-de-database-input-cache"]);
				if (! (isset($postValues["database-cache"]) && UString::isNotNull($postValues["database-cache"]))) {
					$postValues["database-cache"] = false;
				}
			} else {
				$postValues["database-cache"] = false;
			}
			$postValues["debug"] = isset($postValues["debug"]);
			$postValues["test"] = isset($postValues["test"]);
			$postValues["templateEngineOptions-cache"] = isset($postValues["templateEngineOptions-cache"]);
		}
		foreach ($postValues as $key => $value) {
			if (strpos($key, "-") === false) {
				$result[$key] = $value;
			} else {
				list ($k1, $k2) = explode("-", $key);
				if (! isset($result[$k1])) {
					$result[$k1] = [];
				}
				$result[$k1][$k2] = $value;
			}
		}
		$content = "<?php\nreturn " . UArray::asPhpArray($result, "array", 1, true) . ";";
		if (CodeUtils::isValidCode($content)) {
			if (Startup::saveConfig($content)) {
				$this->showSimpleMessage("The configuration file has been successfully modified!", "positive", "check square", null, "msgConfig");
			} else {
				$this->showSimpleMessage("Impossible to write the configuration file.", "negative", "warning circle", null, "msgConfig");
			}
		} else {
			$this->showSimpleMessage("Your configuration contains errors.<br>The configuration file has not been saved.", "negative", "warning circle", null, "msgConfig");
		}

		$config = Startup::reloadConfig();
		if ($partial == "check") {
			if (isset($config["database"]["dbName"])) {
				Startup::reloadServices();
			}
			$this->models(true);
		} else {
			$this->config(false);
		}
	}

	protected function _checkCondition($callback) {
		if (URequest::isPost()) {
			$result = [];
			UResponse::asJSON();
			$value = $_POST["_value"];
			$result["result"] = $callback($value);
			echo json_encode($result);
		}
	}

	public function _checkArray() {
		$this->_checkCondition(function ($value) {
			try {
				$array = eval("return " . $value . ";");
				return is_array($array);
			} catch (\ParseError $e) {
				return false;
			}
		});
	}

	public function _checkDirectory() {
		$folder = URequest::post("_ruleValue");
		$this->_checkCondition(function ($value) use ($folder) {
			$base = Startup::getApplicationDir();
			return file_exists($base . \DS . $folder . \DS . $value);
		});
	}

	public function _checkClass() {
		$parent = URequest::post("_ruleValue");
		$this->_checkCondition(function ($value) use ($parent) {
			try {
				$class = new \ReflectionClass($value);
				return $class->isSubclassOf($parent);
			} catch (\ReflectionException $e) {
				return false;
			}
		});
	}

	private function convert_smart_quotes($string) {
		$search = array(
			chr(145),
			chr(146),
			chr(147),
			chr(148),
			chr(151)
		);

		$replace = array(
			"'",
			"'",
			'"',
			'"',
			'-'
		);

		return str_replace($search, $replace, $string);
	}

	public function _checkDbStatus() {
		$postValues = $_POST;
		$connected = false;
		$db = new Database($postValues["database-type"], $postValues["database-dbName"], $postValues["database-serverName"], $postValues["database-port"], $postValues["database-user"], $postValues["database-password"]);
		try {
			$db->_connect();
			$connected = $db->isConnected();
		} catch (\Exception $e) {
			$errorMsg = $e->getMessage();
			$msg = ((mb_detect_encoding($errorMsg, "UTF-8, ISO-8859-1, ISO-8859-15", "CP1252")) !== "UTF-8") ? utf8_encode($this->convert_smart_quotes($errorMsg)) : ($errorMsg);
			$connected = false;
		}
		$icon = "exclamation triangle red";
		if ($connected) {
			$icon = "check square green";
		}
		$icon = $this->jquery->semantic()->htmlIcon("db-status", $icon);
		if (isset($msg)) {
			$icon->addPopup("Error", $msg);
		} else {
			$icon->addPopup("Success", "Connexion is ok!");
		}
		$this->jquery->execAtLast('$("#db-status").popup("show");');
		echo $icon;
		echo $this->jquery->compile($this->view);
	}
}
