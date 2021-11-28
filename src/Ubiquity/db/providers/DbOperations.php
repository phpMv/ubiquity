<?php


namespace Ubiquity\db\providers;


abstract class DbOperations {
	const CREATE_DATABASE='create-database';
	const CREATE_TABLE='create-table';
	const SELECT_DB='select-db';
	const FIELD='field';
	const ALTER_TABLE='alter-table';
	const FOREIGN_KEY='foreign-key';
	const ALTER_TABLE_KEY='alter-table-key';
	const AUTO_INC='auto-inc';
	const MODIFY_FIELD='modify-field';
	const ADD_FIELD='add-field';
}
