<?php

namespace Ubiquity\controllers\admin;

use Ubiquity\controllers\ControllerBase;
use controllers\Admin;
use Ubiquity\controllers\admin\popo\MaintenanceMode;
use Ajax\semantic\html\modules\HtmlDimmer;
use Ajax\semantic\html\collections\form\HtmlForm;
use Ubiquity\utils\http\URequest;
use Ubiquity\controllers\Startup;
use Ajax\semantic\html\collections\HtmlMessage;
use Ubiquity\utils\base\UArray;
use Ubiquity\utils\base\UFileSystem;
use Ajax\semantic\html\elements\HtmlSegment;
use Ajax\semantic\html\elements\HtmlLabel;

/**
 *
 * @author jcheron <myaddressmail@gmail.com>
 * @property \Ajax\php\ubiquity\JsUtils $jquery
 *
 */
abstract class DefaultMaintenance extends ControllerBase {
	protected $activeMaintenance;
	protected $savedir = 'database';
	protected $hasTimer;
	protected $viewname = '@framework/Admin/maintenance/default.html';

	/**
	 *
	 * @var HtmlDimmer
	 */
	protected $dimmer;

	protected function createHtmlDimmer() {
		$dimmer = $this->jquery->semantic ()->htmlDimmer ( 'maintenance' );
		$dimmer->asIcon ( $this->activeMaintenance->getIcon (), $this->activeMaintenance->getTitle (), $this->activeMaintenance->getMessage () )->asPage ();
		$dimmer->setClosable ( false );
		$this->hasTimer = false;
		if (($t = $this->activeMaintenance->getDuration ()) != null) {
			$this->createTimer ( $dimmer, $t );
		}
		$this->jquery->execAtLast ( '$("#maintenance").dimmer("show");' );
		$this->dimmer = $dimmer;
	}

	protected function createTimer(HtmlDimmer $dimmer, $duration) {
		$seg = new HtmlSegment ( 'remind' );
		$seg->setContent ( [ $this->getLabel ( 'day' ),$this->getLabel ( 'hour' ),$this->getLabel ( 'minute' ),$this->getLabel ( 'second' ) ] );
		$seg->addClass ( 'fluid' );
		$dimmer->addContent ( $seg );
		$this->jquery->counter ( "#remind", $duration, 0, 'timer' );
		$this->jquery->execOn ( 'counter-end', '#remind', '$(this).html("ready!");' );
		$this->hasTimer = true;
	}

	protected function getLabel($type) {
		$lbl = new HtmlLabel ( '', $type );
		$lbl->addDetail ( '' )->setClass ( $type );
		return $lbl->addClass ( 'olive timer' );
	}

	protected function registerEmail($email) {
		$filename = \ROOT . DS . $this->savedir . \DS . $this->activeMaintenance->getId () . '.php';
		$content = [ ];
		if (\file_exists ( $filename )) {
			$content = include $filename;
		} else {
			UFileSystem::safeMkdir ( \dirname ( $filename ) );
		}
		$new = [ $_SERVER ['REMOTE_ADDR'],$_SERVER ['REQUEST_TIME'] ];
		if (isset ( $content [$email] )) {
			$content [$email] [] = $new;
		} else {
			$content [$email] = [ $new ];
		}
		$content = "<?php\nreturn " . UArray::asPhpArray ( $content, "array", 1, true ) . ";";
		return UFileSystem::save ( $filename, $content );
	}

	public function __construct() {
		parent::__construct ();
		$config = Admin::getConfigFile () ['maintenance'];
		$this->activeMaintenance = MaintenanceMode::getActiveMaintenance ( $config );
	}

	public function isValid($action) {
		return isset ( $this->activeMaintenance );
	}

	public function onInvalidControl() {
		parent::onInvalidControl ();
		echo "No active maintenance!";
		exit ();
	}

	/**
	 * Default maintenance type
	 */
	public function index() {
		$this->createHtmlDimmer ();
		echo $this->jquery->renderView ( $this->viewname );
	}

	/**
	 * ComingSoon maintenance type.
	 * Add the possibility to be notified by email
	 * E-mails are stored in the app/database folder
	 */
	public function comingSoon() {
		$this->createHtmlDimmer ();
		if (URequest::isPost ()) {
			$mail = URequest::post ( 'mail' );
			if ($this->registerEmail ( $mail )) {
				$msg = "Thank you <b>{$mail}</b>. You will be informed soon of the startup.";
			} else {
				$msg = 'Impossible!';
			}
			$this->dimmer->wrap ( new HtmlMessage ( '', $msg ) );
			if ($this->hasTimer) {
				$this->jquery->clearInterval ( 'timer', true );
			}
		} else {
			$ctrl = Startup::getControllerSimpleName ();
			$frm = new HtmlForm ( 'frm-register' );
			$input = $frm->addInput ( 'mail', null, 'mail', null, 'Enter your email' );
			$frm->addFieldRules ( 0, [ 'empty','email' ] );
			$frm->setValidationParams ( [ "on" => "blur","inline" => true ] );
			$frm->setSubmitParams ( $ctrl . '/comingSoon', '#frm-register', [ 'hasLoader' => 'internal' ] );
			$input->addAction ( 'Notify me', 'right', 'mail' );
			$this->dimmer->addContent ( $frm );
		}
		echo $this->jquery->renderView ( $this->viewname );
	}
}

