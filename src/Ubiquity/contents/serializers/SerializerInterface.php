<?php

namespace Ubiquity\contents\serializers;

/**
 * Ubiquity\contents\serializers$SerializerInterface
 * This class is part of Ubiquity
 *
 * @author jc
 * @version 1.0.1
 *
 */
interface SerializerInterface {

	/**
	 *
	 * @param object $object
	 */
	public function serialize($object);

	/**
	 *
	 * @param string $serial
	 * @return array|object
	 */
	public function unserialize($serial);
}

