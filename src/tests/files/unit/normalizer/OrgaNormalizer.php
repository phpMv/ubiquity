<?php

namespace normalizer;

use Ubiquity\contents\normalizers\NormalizerInterface;
use Ubiquity\contents\normalizers\NormalizersManager;
use Ubiquity\translation\TranslatorManager;
use models\Organization;

class OrgaNormalizer implements NormalizerInterface {

	public function supportsNormalization($data) {
		return $data instanceof Organization;
	}

	public function normalize($object) {
		return [ 'id' => $object->getId (),'name' => $object->getName (),'domain' => $object->getDomain (),'aliases' => $object->getAliases (),'translated' => TranslatorManager::trans ( 'translated.1000', [ ], 'phpbenchmarks' ),'users' => NormalizersManager::normalizeArray_ ( $object->getUsers () ) ];
	}
}

