<?php

namespace Ubiquity\controllers\semantic;

use Ajax\semantic\html\elements\HtmlButton;
use Ubiquity\utils\base\UString;
use Ajax\semantic\html\elements\HtmlDivider;
use Ajax\semantic\html\base\HtmlSemDoubleElement;
use Ajax\semantic\html\collections\HtmlMessage;
use Ubiquity\controllers\crud\CRUDMessage;
use Ubiquity\controllers\crud\viewers\ModelViewer;

/**
 *
 * @author jc
 * @property \Ajax\php\ubiquity\JsUtils $jquery
 *
 */
trait MessagesTrait {

	/**
	 *
	 * @return ModelViewer
	 */
	abstract protected function _getModelViewer();

	abstract public function _getFiles();

	protected function showSimpleMessage_(CRUDMessage $message, $staticName = null, $toast = false): HtmlMessage {
		return $this->_showSimpleMessage ( $message->getMessage (), $message->getType (), $message->getTitle (), $message->getIcon (), $message->getTimeout (), $staticName, null, $toast );
	}

	public function _showSimpleMessage($content, $type, $title = null, $icon = "info", $timeout = NULL, $staticName = null, $closeAction = null, $toast = false): HtmlMessage {
		$semantic = $this->jquery->semantic ();
		if (! isset ( $staticName ))
			$staticName = "msg-" . rand ( 0, 50 );
			if(isset($this->style)){
				$type.=' '.$this->style;
			}
		$message = $semantic->htmlMessage ( $staticName, $content, $type );
		if (isset ( $title )) {
			$message->addHeader ( $title );
		}
		if (isset ( $icon )) {
			$message->setIcon ( $icon );
		}
		if ($timeout !== '') {
			$message->setDismissable ();
		}
		if ($timeout != null) {
			$message->setTimeout ( 3000 );
		} elseif (isset ( $closeAction )) {
			$message->getOnClose ( $this->_getFiles ()->getAdminBaseRoute () . "/_closeMessage/" . $closeAction );
		}
		if ($toast) {
			$message->asToast ();
		}
		return $message;
	}

	protected function showConfMessage_(CRUDMessage $message, $url, $responseElement, $data, $attributes = NULL): HtmlMessage {
		return $this->_showConfMessage ( $message->getMessage (), $message->getType (), $message->getTitle (), $message->getIcon (), $url, $responseElement, $data, $attributes );
	}

	protected function _showConfMessage($content, $type, $title, $icon, $url, $responseElement, $data, $attributes = NULL): HtmlMessage {
		$messageDlg = $this->_showSimpleMessage ( $content, $type, $title, $icon );
		$btOkay = new HtmlButton ( "bt-okay", "Confirm", "negative" );
		$btOkay->addIcon ( "check circle" );
		$btOkay->postOnClick ( $url, "{data:'" . $data . "'}", $responseElement, $attributes );
		$btCancel = new HtmlButton ( "bt-cancel-" . UString::cleanAttribute ( $url ), "Cancel" );
		$btCancel->addIcon ( "remove circle outline" );
		$btCancel->onClick ( $messageDlg->jsHide () );
		$messageDlg->addContent ( [ new HtmlDivider ( "" ),new HtmlSemDoubleElement ( "", "div", "", [ $btOkay->floatRight (),$btCancel->floatRight () ] ) ] );
		$this->_getModelViewer ()->onConfirmButtons ( $btOkay, $btCancel );
		return $messageDlg;
	}
}

