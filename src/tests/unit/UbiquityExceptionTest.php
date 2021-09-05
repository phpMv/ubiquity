<?php
use Ubiquity\exceptions\UbiquityException;
use Ubiquity\exceptions\CacheException;
use Ubiquity\exceptions\DAOException;
use Ubiquity\exceptions\DBException;
use Ubiquity\exceptions\DiException;
use Ubiquity\exceptions\NormalizerException;
use Ubiquity\exceptions\RestException;
use Ubiquity\exceptions\RouterException;
use Ubiquity\exceptions\ThemesException;
use Ubiquity\exceptions\TransformerException;
use Ubiquity\exceptions\ValidatorException;


/**
 * UbiquityException test case.
 */
class UbiquityExceptionTest extends \Codeception\Test\Unit {

	/**
	 * Tests UbiquityException->__toString()
	 */
	public function test__toString() {
		$exArray=[UbiquityException::class=>'Ubiquity exception',DAOException::class=>'DAO exception',DBException::class=>'DB exception',
				DiException::class=>'Di exception',NormalizerException::class=>'Normalizer exception',RestException::class=>'Rest exception',
				RouterException::class=>'Router exception',ThemesException::class=>'Themes exception',TransformerException::class=>'Transformer exception',
				ValidatorException::class=>'Validator exception'
		];
		foreach ($exArray as $class=>$message){
			$e=new $class($message,500);
			$this->assertStringContainsString($message,($e)."");
		}

	}
}

