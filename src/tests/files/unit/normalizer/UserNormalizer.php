<?php

namespace normalizer;

use Ubiquity\contents\normalizers\NormalizerInterface;
use Ubiquity\translation\TranslatorManager;
use models\User;

class UserNormalizer implements NormalizerInterface {

	public function supportsNormalization($data) {
		return $data instanceof User;
	}

	public function normalize($object) {
		return [ 'id' => $object->getId (),'email' => $object->getEmail (),'firstname' => $object->getFirstname (),'lastname' => $object->getLastname (),'password' => $object->getPassword (),'translated' => TranslatorManager::trans ( 'translated.2000', [ ], 'phpbenchmarks' ) ];
	}
}

