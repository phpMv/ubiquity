<?php
namespace Ubiquity\events;

/**
 * Rest events constants
 *
 * \Ubiquity\events$RestEvents
 * This class is part of Ubiquity
 *
 * @author jcheron <myaddressmail@gmail.com>
 * @version 1.0.2
 *
 */
class RestEvents {

	const BEFORE_INSERT = 'rest.before.insert';

	const BEFORE_UPDATE = 'rest.before.update';
	
	const BEFORE_DELETE = 'rest.before.delete';
	
	const BEFORE_GET_ONE = 'rest.before.get.one';
	
	const BEFORE_GET_ALL = 'rest.before.get.all';
}

