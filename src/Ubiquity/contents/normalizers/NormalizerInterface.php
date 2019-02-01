<?php

namespace Ubiquity\contents\normalizers;

interface NormalizerInterface {
	public function normalize($object);
	public function supportsNormalization($data);
}

