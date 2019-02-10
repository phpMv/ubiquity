<?php

namespace Ubiquity\log;

class LoggerParams {
	const DAO = "DAO";
	const DATABASE = "Database";
	const ROUTER = "Router";
	const CACHE = "Cache";
	const REST = "Rest";
	const STARTUP = "Startup";
	const TRANSLATE = "Translate";
	public static $contexts = [ self::DAO,self::DATABASE,self::ROUTER,self::CACHE,self::REST,self::STARTUP,self::TRANSLATE ];
}

