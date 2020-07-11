<?php

namespace Ubiquity\contents\serializers;

/**
 * Ubiquity\contents\serializers$JsonSerializer
 * This class is part of Ubiquity
 *
 * @author jc
 * @version 1.0.0
 *
 */
class JsonSerializer implements SerializerInterface {

	public function serialize($object) {
		return \json_encode ( [ 'class' => \get_class ( $object ),'o' => $object ] );
	}

	public function unserialize($serial) {
		$datas = \json_decode ( $serial );
		$class = $datas->class;
		$stdObj = $datas->o;
		$count = \strlen ( $class );
		$temp = \serialize ( $stdObj );
		$temp = \preg_replace ( "@^O:8:\"stdClass\":@", "O:$count:\"$class\":", $temp );
		$o = \unserialize ( $temp );
		$o->_rest = ( array ) ($o->_rest);
		return $o;
	}
}

