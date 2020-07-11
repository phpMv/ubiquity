<?php

namespace Ubiquity\contents\serializers;

/**
 * Ubiquity\contents\serializers$PhpSerializer
 * This class is part of Ubiquity
 *
 * @author jc
 * @version 1.0.0
 *
 */
class PhpSerializer implements SerializerInterface {

	public function serialize($object) {
		return \serialize ( $object );
	}

	public function unserialize($serial) {
		return \unserialize ( $serial );
	}
}

