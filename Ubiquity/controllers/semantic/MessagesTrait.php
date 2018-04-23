<?php

namespace Ubiquity\controllers\semantic;

use Ajax\semantic\html\elements\HtmlButton;
use Ubiquity\utils\base\UString;
use Ajax\semantic\html\elements\HtmlDivider;
use Ajax\semantic\html\base\HtmlSemDoubleElement;
use Ajax\semantic\html\collections\HtmlMessage;
use Ajax\JsUtils;

/**
 * @author jc
 * @property JsUtils $jquery
 *
 */
trait MessagesTrait {
	protected function showSimpleMessage($content, $type, $icon = "info", $timeout = NULL, $staticName = null): HtmlMessage {
		$semantic = $this->jquery->semantic ();
		if (! isset ( $staticName ))
			$staticName = "msg-" . rand ( 0, 50 );
			$message = $semantic->htmlMessage ( $staticName, $content, $type );
			$message->setIcon ( $icon );
			$message->setDismissable ();
			if (isset ( $timeout ))
				$message->setTimeout ( 3000 );
				return $message;
	}
	
	protected function showConfMessage($content, $type, $url, $responseElement, $data, $attributes = NULL) {
		$messageDlg = $this->showSimpleMessage ( $content, $type, "help circle" );
		$btOkay = new HtmlButton( "bt-okay", "Confirm", "negative" );
		$btOkay->addIcon ( "check circle" );
		$btOkay->postOnClick ( $url, "{data:'" . $data . "'}", $responseElement, $attributes );
		$btCancel = new HtmlButton ( "bt-cancel-" . UString::cleanAttribute ( $url ), "Cancel" );
		$btCancel->addIcon ( "remove circle outline" );
		$btCancel->onClick ( $messageDlg->jsHide () );
		$messageDlg->addContent ( [ new HtmlDivider( "" ),new HtmlSemDoubleElement( "", "div", "", [ $btOkay->floatRight (),$btCancel->floatRight () ] ) ] );
		return $messageDlg;
	}
}

