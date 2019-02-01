<?php

namespace Ubiquity\translation\loader;

interface LoaderInterface {
	public function load($locale, $domain = 'messages');
	public function save($messages,$locale,$domain);
	public function clearCache($locale=null,$domain=null);
}

