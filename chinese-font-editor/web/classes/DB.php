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

	/**
	 * Insert object with values from settings.
	 *
	 * Will break on strange column or table names, as it does no escaping.
	 *
	 * @param string table_name Table name
	 * @param array values Associative array of values.
	 * @param array types Associative array of types (values are PDO::PARAM_...).
	 * All columns should have their types passed.
	 * @param array [defaults] Associative array of default values.
	 * @return bool Whether the insert was successful.
	 */
	static function insert(string $table_name, array $values, array $types, array $defaults = []) {
		$all_values = array_merge($defaults, $values);

		$columns = [];
		$placeholders = [];

		foreach ($all_values as $key => $value) {
			if (!array_key_exists($key, $types)) continue;
			$columns[] = $key;
			$placeholders[] = ':' . $key;
		}
		$columns_sql = implode(',', $columns);
		$placeholders_sql = implode(', ', $placeholders);

		$sql = "
			INSERT INTO ${table_name}($columns_sql)
			VALUES (${placeholders_sql});
		";
		
		$db = self::get();
		$stmt = $db->prepare($sql);
		foreach ($all_values as $key => $value) {
			if (!array_key_exists($key, $types)) continue;
			$stmt->bindValue(':' . $key, $value, $types[$key]);
		}
		return $stmt->execute();
	}
}
