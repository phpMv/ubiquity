<?php

/**
 * Normalizers managment
 */
namespace Ubiquity\contents\normalizers;

/**
 * Defines the default behavior of a Normalizer
 * Ubiquity\contents\normalizers$NormalizerInterface
 * This class is part of Ubiquity
 *
 * @author jcheron <myaddressmail@gmail.com>
 * @version 1.0.0
 *
 */
interface NormalizerInterface {

	public function normalize($object);

	public function supportsNormalization($data);
}

