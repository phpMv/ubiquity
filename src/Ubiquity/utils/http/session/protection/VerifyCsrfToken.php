<?php

namespace Ubiquity\utils\http\session\protection;

use Ubiquity\utils\http\session\AbstractSession;
use Ubiquity\utils\http\UCookie;
use Ubiquity\log\Logger;

/**
 * Ubiquity\utils\http\session\protection$VerifyCsrfToken
 * This class is part of Ubiquity
 *
 * @author jc
 * @version 1.0.0
 *
 */
class VerifyCsrfToken implements VerifySessionCsrfInterface {
	private AbstractSession $sessionInstance;
	private const TOKEN_KEY = 'X-XSRF-TOKEN';

	public function __construct(AbstractSession $sessionInstance) {
		$this->sessionInstance = $sessionInstance;
	}

	protected function csrfErrorLog() {
		$context = array ();
		$context ['HOST'] = $_SERVER ['HTTP_HOST'];
		$context ['REQUEST_URI'] = $_SERVER ['REQUEST_URI'];
		$context ['REQUEST_METHOD'] = $_SERVER ['REQUEST_METHOD'];
		$context ['cookie'] = $_COOKIE;
		Logger::error ( 'Session', 'CSRF protector validation failure!', 'startSession', $context );
	}

	public function init() {
		$token = new CsrfToken ();
		$this->sessionInstance->set ( self::TOKEN_KEY, $token );
		UCookie::set ( $token->getName (), $token->getValue (), null );
	}

	public function clear() {
		$token = $this->sessionInstance->get ( self::TOKEN_KEY );
		$this->sessionInstance->delete ( self::TOKEN_KEY );
		if (isset ( $token )) {
			UCookie::delete ( $token->getName () );
		}
	}

	public function start() {
		$token = $this->sessionInstance->get ( self::TOKEN_KEY );
		if (isset ( $token )) {
			if (! hash_equals ( $token->getValue (), UCookie::get ( $token->getName () ) )) {
				if (Logger::isActive ()) {
					$this->csrfErrorLog ();
				}
				$this->sessionInstance->terminate ();
			} else {
				return;
			}
		}
		$this->init ();
	}
}

