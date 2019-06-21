<?php

namespace Ubiquity\controllers\admin\traits;

use Ubiquity\controllers\Startup;
use Ubiquity\orm\DAO;
use Ubiquity\utils\base\UString;
use Ubiquity\cache\CacheManager;
use Ubiquity\cache\ClassUtils;
use Ubiquity\controllers\admin\popo\InfoMessage;
use Ubiquity\db\Database;
use Ajax\semantic\html\base\HtmlSemDoubleElement;
use Ubiquity\utils\base\UFileSystem;
use Ubiquity\cache\system\ArrayCache;
use Ubiquity\orm\creator\database\DbModelsCreator;
use Ubiquity\controllers\admin\UbiquityMyAdminFiles;
use Ajax\semantic\html\collections\HtmlMessage;
use Ubiquity\utils\base\UArray;
use Ajax\semantic\html\modules\HtmlDropdown;
use Ubiquity\orm\parser\Reflexion;

/**
 *
 * @author jc
 * @property array $steps
 * @property int $activeStep
 * @property string $engineering
 * @property \Ajax\JsUtils $jquery
 */
trait CheckTrait {
	protected $messages = [ "error" => [ ],"info" => [ ] ];

	abstract protected function getModelSteps();

	abstract protected function getActiveModelStep();

	abstract protected function getNextModelStep();

	abstract protected function displayModelsMessages($type, $messagesToDisplay);

	abstract protected function showSimpleMessage($content, $type, $title = null, $icon = "info", $timeout = NULL, $staticName = null): HtmlMessage;

	abstract public function _isModelsCompleted();

	/**
	 *
	 * @return UbiquityMyAdminFiles
	 */
	abstract public function _getFiles();

	public function createModels($singleTable = null) {
		$config = Startup::getConfig ();
		\ob_start ();
		(new DbModelsCreator ())->create ( $config, false, $singleTable );
		$result = \ob_get_clean ();
		$message = $this->showSimpleMessage ( "", "success", "Models creation", "check mark", null, "msg-create-models" );
		$message->addList ( \explode ( "\n", \str_replace ( "\n\n", "\n", \trim ( $result ) ) ) );
		$this->models ( true );
		echo $message;
	}

	protected function _checkStep($niveau = null) {
		$nbChecked = 1;
		$niveauMax = $niveau;
		if (! isset ( $niveau ))
			$niveauMax = $this->activeStep - 1;
		$steps = $this->getModelSteps ();
		while ( $nbChecked <= $niveauMax && $this->hasNoError () && isset ( $steps [$nbChecked] ) ) {
			$this->_modelCheckOneNiveau ( $steps [$nbChecked] [1] );
			$nbChecked ++;
		}
		if ($this->hasError () && ! isset ( $niveau )) {
			$this->activeStep = $nbChecked - 1;
			$this->steps [$this->engineering] [$this->activeStep] [0] = "warning sign red";
		}
	}

	protected function _modelCheckOneNiveau($name) {
		$config = Startup::getConfig ();
		switch ($name) {
			case "Conf" :
				if ($this->missingKeyInConfigMessage ( "Database is not well configured in <b>app/config/config.php</b>", Startup::checkDbConfig () ) === false) {
					$this->_addInfoMessage ( "settings", "Database is well configured" );
				}
				break;
			case "Connexion" :
			case "Database" :
				$this->checkDatabase ( $config, "database" );
				break;
			case "Models" :
				CacheManager::start ( $config );
				$this->checkModels ( $config );
				if ($this->engineering === "forward") {
					$modelsWithoutFiles = $this->getModelsWithoutTable ( $config );
					if (sizeof ( $modelsWithoutFiles ) > 0) {
						foreach ( $modelsWithoutFiles as $model ) {
							$table = Reflexion::getTableName ( $model );
							$this->_addErrorMessage ( "warning", "The table <b>" . $table . "</b> does not exists for the model <b>" . $model . "</b>." );
						}
					}
				}
				break;
			case "Cache" :
				$this->checkModelsCache ( $config );
				break;
		}
	}

	protected function checkDatabase($config, $infoIcon = "database") {
		$db = $config ["database"];
		if (! DAO::isConnected ()) {
			$this->_addErrorMessage ( "warning", "connection to the database is not established (probably in <b>app/config/services.php</b> file)." );
			try {
				if ($db ["dbName"] !== "") {
					$this->_addInfoMessage ( $infoIcon, "Attempt to connect to the database <b>" . $db ["dbName"] . "</b> ..." );
					$db = new Database ( $db ["type"], $db ["dbName"], @$db ["serverName"], @$db ["port"], @$db ["user"], @$db ["password"], @$db ["options"], @$db ["cache"] );
					$db->connect ();
				}
			} catch ( \Exception $e ) {
				$this->_addErrorMessage ( "warning", $e->getMessage () );
			}
		} else {
			$this->_addInfoMessage ( $infoIcon, "The connection to the database <b>" . $db ["dbName"] . "</b> is established." );
		}
	}

	protected function checkModels($config, $infoIcon = "sticky note") {
		if ($this->missingKeyInConfigMessage ( "Models namespace is not well configured in <b>app/config/config.php</b>", Startup::checkModelsConfig () ) === false) {
			$modelsNS = @$config ["mvcNS"] ["models"];
			$this->_addInfoMessage ( $infoIcon, "Models namespace <b>" . $modelsNS . "</b> is ok." );
			$dir = UFileSystem::cleanPathname ( \ROOT . \DS . $modelsNS );
			if (\file_exists ( $dir ) === false) {
				$this->_addErrorMessage ( "warning", "The directory <b>" . $dir . "</b> does not exists." );
			} else {
				$this->_addInfoMessage ( $infoIcon, "The directory for models namespace <b>" . $dir . "</b> exists." );
				$files = CacheManager::getModelsFiles ( $config, true );
				if (\sizeof ( $files ) === 0) {
					$this->_addErrorMessage ( "warning", "No file found in <b>" . $dir . "</b> folder." );
				} else {
					foreach ( $files as $file ) {
						$completeName = ClassUtils::getClassFullNameFromFile ( $file );
						$parts = \explode ( "\\", $completeName );
						$classname = \array_pop ( $parts );
						$ns = \implode ( "\\", $parts );
						if ($ns !== $modelsNS) {
							$this->_addErrorMessage ( "warning", "The namespace <b>" . $ns . "</b> would be <b>" . $modelsNS . "</b> for the class <b>" . $classname . "</b>." );
						} else {
							$this->_addInfoMessage ( $infoIcon, "The namespace for the class <b>" . $classname . "</b> is ok." );
						}
					}
				}
			}
		}
	}

	protected function getTablesWithoutModel($config) {
		$models = CacheManager::getModels ( $config, true );
		$tables = DAO::$db->getTablesName ();
		$allJoinTables = Reflexion::getAllJoinTables ( $models );
		$tables = array_diff ( $tables, $allJoinTables );
		foreach ( $models as $model ) {
			try {
				$table = Reflexion::getTableName ( $model );
				$tables = UArray::iRemove ( $tables, $table );
			} catch ( \Exception $e ) {
			}
		}
		return $tables;
	}

	protected function getModelsWithoutTable($config) {
		$models = CacheManager::getModels ( $config, true );
		$result = $models;
		$tables = DAO::$db->getTablesName ();
		$allJoinTables = Reflexion::getAllJoinTables ( $models );
		$tables = array_diff ( $tables, $allJoinTables );
		foreach ( $models as $model ) {
			if (sizeof ( Reflexion::getAnnotationClass ( $model, "@transient" ) ) == 0) {
				try {
					$table = Reflexion::getTableName ( $model );
					if ((UArray::iSearch ( $table, $tables, false )) !== false) {
						$result = UArray::iRemoveOne ( $result, $model );
					}
				} catch ( \Exception $e ) {
				}
			} else {
				$result = UArray::iRemoveOne ( $result, $model );
			}
		}
		return $result;
	}

	protected function checkModelsCache($config, $infoIcon = "lightning") {
		$instanceCache = CacheManager::$cache;
		if ($instanceCache === null) {
			$this->_addErrorMessage ( "warning", "Cache instance is not created." );
		} else {
			$this->_addInfoMessage ( $infoIcon, $instanceCache->getCacheInfo () );
			if (CacheManager::$cache instanceof ArrayCache) {
				$this->checkArrayCache ( $config, $infoIcon );
			} else {
				$this->checkModelsCacheFiles ( $config, $infoIcon );
			}
		}
	}

	protected function checkArrayCache($config, $infoIcon = "lightning") {
		if (! isset ( $config ["cache"] ) || UString::isNull ( $config ["cache"] )) {
			self::missingKeyInConfigMessage ( "Cache directory is not well configured in <b>app/config/config.php</b>", [ "cache" ] );
		} else {
			if (! isset ( $config ["cache"] ["directory"] ) || UString::isNull ( $config ["cache"] ["directory"] )) {
				self::missingKeyInConfigMessage ( "Cache directory is not well configured in <b>app/config/config.php</b>", [ "directory" ] );
			} else {
				$cacheDir = UFileSystem::cleanPathname ( \ROOT . \DS . $config ["cache"] ["directory"] );
				$this->_addInfoMessage ( $infoIcon, "Models cache directory is well configured in config file." );
				$cacheDirs = CacheManager::getCacheDirectories ( $config, true );
				if (\file_exists ( $cacheDir ) === false) {
					$this->_addErrorMessage ( "warning", "The cache directory <b>" . $cacheDir . "</b> does not exists." );
				} else {
					$modelsCacheDir = UFileSystem::cleanPathname ( $cacheDirs ["models"] );
					$this->_addInfoMessage ( $infoIcon, "Cache directory <b>" . $cacheDir . "</b> exists." );
					if (\file_exists ( $modelsCacheDir ) === false) {
						$this->_addErrorMessage ( "warning", "The models cache directory <b>" . $modelsCacheDir . "</b> does not exists." );
					} else {
						$this->_addInfoMessage ( $infoIcon, "Models cache directory <b>" . $modelsCacheDir . "</b> exists." );
						$this->checkModelsCacheFiles ( $config, $infoIcon );
					}
				}
			}
		}
	}

	protected function checkModelsCacheFiles($config, $infoIcon = "lightning") {
		CacheManager::startProd ( $config );
		$files = CacheManager::getModelsFiles ( $config, true );
		foreach ( $files as $file ) {
			$classname = ClassUtils::getClassFullNameFromFile ( $file );
			if (! CacheManager::modelCacheExists ( $classname )) {
				$this->_addErrorMessage ( "warning", "The models cache entry does not exists for the class <b>" . $classname . "</b>." );
			} else {
				$this->_addInfoMessage ( $infoIcon, "The models cache entry for <b>" . $classname . "</b> exists." );
			}
		}
	}

	protected function displayAllMessages() {
		if ($this->hasNoError ()) {
			$this->_addInfoMessage ( "checkmark", "everything is fine here" );
		}
		if ($this->hasMessages ()) {
			$messagesElmInfo = $this->displayModelsMessages ( $this->hasNoError () ? "success" : "info", $this->messages ["info"] );
			echo $messagesElmInfo;
		}
		if ($this->hasError ()) {
			$messagesElmError = $this->displayModelsMessages ( "error", $this->messages ["error"] );
			echo $messagesElmError;
		}
		$this->showActions ();
	}

	protected function showActions() {
		$buttons = $this->jquery->semantic ()->htmlButtonGroups ( "step-actions" );
		$step = $this->getActiveModelStep ();
		switch ($step [1]) {
			case "Conf" :
				$buttons->addItem ( "Show config file" )->getOnClick ( $this->_getFiles ()->getAdminBaseRoute () . "/_config", "#action-response" )->addIcon ( "settings" );
				$buttons->addItem ( "Edit config file" )->addClass ( "orange" )->getOnClick ( $this->_getFiles ()->getAdminBaseRoute () . "/formConfig/check", "#action-response" )->addIcon ( "edit" );
				break;
			case "Connexion" :
			case "Database" :
				if ($this->engineering === "reverse")
					$buttons->addItem ( "(Re-)Create database" )->getOnClick ( $this->_getFiles ()->getAdminBaseRoute () . "/showDatabaseCreation", "#main-content" )->addIcon ( "database" );
				break;
			case "Models" :
				if ($this->engineering === "forward") {
					if (sizeof ( $tables = $this->getTablesWithoutModel ( Startup::getConfig () ) )) {
						$ddBtn = new HtmlDropdown ( "ddTables", "Create models for new tables", array_combine ( $tables, $tables ) );
						$ddBtn->asButton ();
						$ddBtn->getOnClick ( $this->_getFiles ()->getAdminBaseRoute () . "/createModels", "#main-content", [ "attr" => "data-value" ] );
						$buttons->addItem ( $ddBtn );
					}
					$buttons->addItem ( "(Re-)Create all models" )->getOnClick ( $this->_getFiles ()->getAdminBaseRoute () . "/createModels", "#main-content", [ "attr" => "" ] )->addIcon ( "sticky note" );
				} else {
					$buttons->addItem ( "Import from Yuml" )->getOnClick ( $this->_getFiles ()->getAdminBaseRoute () . "/_importFromYuml", "#models-main", [ "attr" => "" ] )->addIcon ( "sticky note" );
				}
				$bt = $buttons->addItem ( "Classes diagram" )->getOnClick ( $this->_getFiles ()->getAdminBaseRoute () . "/_showAllClassesDiagram", "#action-response", [ "attr" => "","ajaxTransition" => "random" ] );
				$bt->addIcon ( "sticky note outline" );
				if ($this->hasError ())
					$bt->addClass ( "disabled" );
				break;
			case "Cache" :
				$buttons->addItem ( "(Re-)Init all models cache" )->getOnClick ( $this->_getFiles ()->getAdminBaseRoute () . "/_initCache/models/models", "#main-content" )->addIcon ( "lightning" );
				break;
		}
		$nextStep = $this->getNextModelStep ();
		if (isset ( $nextStep )) {
			$bt = $buttons->addItem ( $nextStep [1] );
			$bt->addIcon ( "angle double right", false );
			$bt->addLabel ( $nextStep [2], true, $nextStep [0] );
			$bt->getContent () [1]->addClass ( "green" );
			if ($this->hasNoError ()) {
				$bt->getOnClick ( $this->_getFiles ()->getAdminBaseRoute () . "/_loadModelStep/" . $this->engineering . "/" . ($this->activeStep + 1), "#models-main" );
			} else {
				$bt->addClass ( "disabled" );
			}
		} else {
			$bt = $buttons->addItem ( "See datas" )->addClass ( "black" );
			$bt->addIcon ( "unhide" );
			if ($this->hasNoError ()) {
				$bt->getOnClick ( $this->_getFiles ()->getAdminBaseRoute () . "/models/noHeader/", "#models-main" );
			} else {
				$bt->addClass ( "disabled" );
			}
		}
		echo "<div>" . $buttons . "</div><br>";
		echo "<div id='action-response'></div>";
	}

	protected function missingKeyInConfigMessage($message, $keys) {
		if (\sizeof ( $keys ) > 0) {
			$this->_addErrorMessage ( "warning", $message . " : parameters <b>[" . \Ajax\service\JArray::implode ( ",", $keys ) . "]</b> are required." );
			return true;
		}
		return false;
	}

	protected function _addErrorMessage($type, $content) {
		$this->_addMessage ( "error", $type, $content );
	}

	protected function _addInfoMessage($type, $content) {
		$this->_addMessage ( "info", $type, $content );
	}

	protected function _addMessage($key, $type, $content) {
		$this->messages [$key] [] = new InfoMessage ( $type, $content );
	}

	protected function hasError() {
		return \sizeof ( $this->messages ["error"] ) > 0;
	}

	protected function hasNoError() {
		return \sizeof ( $this->messages ["error"] ) == 0;
	}

	protected function hasMessages() {
		return \sizeof ( $this->messages ["info"] ) > 0;
	}

	protected function displayMessages($type, $messagesToDisplay, $header = "", $icon = "") {
		$messagesElm = $this->jquery->semantic ()->htmlMessage ( "modelsMessages-" . $type );
		$messagesElm->addHeader ( $header );
		if ($this->hasError () && $type === "info")
			$messagesElm->setIcon ( "info circle" );
		else
			$messagesElm->setIcon ( $icon );
		$messages = [ ];
		foreach ( $messagesToDisplay as $msg ) {
			$elm = new HtmlSemDoubleElement ( "", "li", "", $msg->getContent () );
			$elm->addIcon ( $msg->getType () );
			$messages [] = $elm;
		}
		$messagesElm->addList ( $messages );
		$messagesElm->addClass ( $type );
		return $messagesElm;
	}
}
