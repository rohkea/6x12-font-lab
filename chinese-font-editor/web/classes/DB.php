<?php
namespace FontEditor;

/**
 * Generic class for handling the database.
 */
class DB {
	private static $database;

	private function __construct() {}

	/**
	 * Connect to the database, if not yet connected, and return the single PDO
	 * connection instance.
	 * @return \PDO
	 */
	static function get() {
		if (!self::$database) {
			$connectionString = 'sqlite:' . SQLITE_FONT_DB;
			self::$database = new \PDO($connectionString);
		}
		return self::$database;
	}
}
