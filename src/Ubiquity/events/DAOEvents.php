<?php

namespace Ubiquity\events;

/**
 * DAO events constants
 *
 * src\Ubiquity\events$DAOEvents
 * This class is part of Ubiquity
 *
 * @author jcheron <myaddressmail@gmail.com>
 * @version 1.0.0
 *
 */
class DAOEvents {
	const GET_ALL = 'dao.getall';
	const GET_ONE = 'dao.getone';
	const AFTER_INSERT = 'dao.after.insert';
	const AFTER_UPDATE = 'dao.after.update';
}

