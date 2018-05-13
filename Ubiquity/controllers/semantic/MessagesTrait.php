<?php

namespace Ubiquity\controllers\semantic;

use Ajax\semantic\html\elements\HtmlButton;
use Ubiquity\utils\base\UString;
use Ajax\semantic\html\elements\HtmlDivider;
use Ajax\semantic\html\base\HtmlSemDoubleElement;
use Ajax\semantic\html\collections\HtmlMessage;
use Ubiquity\controllers\crud\CRUDMessage;
use Ubiquity\controllers\admin\viewers\ModelViewer;

/**
 * @author jc
 * @property \Ajax\php\ubiquity\JsUtils $jquery
 *
 */
trait MessagesTrait {
	
	/**
	 * @return ModelViewer
	 */
	abstract public function _getModelViewer();
	
	protected function _showSimpleMessage(CRUDMessage $message,$staticName=null):HtmlMessage{
		return $this->showSimpleMessage($message->getMessage(), $message->getType(),$message->getTitle(),$message->getIcon(),$message->getTimeout(),$staticName);
	}
	
	public function showSimpleMessage($content, $type, $title=null,$icon = "info", $timeout = NULL, $staticName = null): HtmlMessage {
		$semantic = $this->jquery->semantic ();
		if (! isset ( $staticName ))
			$staticName = "msg-" . rand ( 0, 50 );
		$message = $semantic->htmlMessage ( $staticName, $content, $type );
		if(isset($title)){
			$message->addHeader($title);
		}
		$message->setIcon ( $icon );
		$message->setDismissable ();
		if (isset ( $timeout ))
			$message->setTimeout ( 3000 );
		return $message;
	}
	
	protected function _showConfMessage(CRUDMessage $message,$url, $responseElement, $data, $attributes = NULL):HtmlMessage {
		return $this->showConfMessage($message->getMessage(), $message->getType(), $message->getTitle(), $url, $responseElement, $data,$attributes);
	}
	
	public function showConfMessage($content, $type, $title,$url, $responseElement, $data, $attributes = NULL):HtmlMessage {
		$messageDlg = $this->showSimpleMessage ( $content, $type,$title, "help circle" );
		$btOkay = new HtmlButton( "bt-okay", "Confirm", "negative" );
		$btOkay->addIcon ( "check circle" );
		$btOkay->postOnClick ( $url, "{data:'" . $data . "'}", $responseElement, $attributes );
		$btCancel = new HtmlButton ( "bt-cancel-" . UString::cleanAttribute ( $url ), "Cancel" );
		$btCancel->addIcon ( "remove circle outline" );
		$btCancel->onClick ( $messageDlg->jsHide () );
		$messageDlg->addContent ( [ new HtmlDivider( "" ),new HtmlSemDoubleElement( "", "div", "", [ $btOkay->floatRight (),$btCancel->floatRight () ] ) ] );
		$this->_getModelViewer()->confirmButtons($btOkay, $btCancel);
		return $messageDlg;
	}
}

