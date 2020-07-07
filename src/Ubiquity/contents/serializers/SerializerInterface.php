<?php

namespace Ubiquity\contents\serializers;

/**
 * Ubiquity\contents\serializers$SerializerInterface
 * This class is part of Ubiquity
 *
 * @author jc
 * @version 1.0.0
 *
 */
interface SerializerInterface {

	public function serialize($object);

	public function unserialize($serial): object;
}

