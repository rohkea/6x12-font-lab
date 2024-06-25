<?php
namespace FontEditor;

/**
 * Static class for requesting font information.
 */
class FontModel {
	/**
	 * Retrieve the data about a specific cont
	 * @param string $font_code Short code of the font
	 * @return \stdClass
	 */
	public static function getByCode(string $font_code) {
		$db = DB::get();
		$stmt = $db->prepare('SELECT * FROM fonts WHERE code = :font_code');
		$stmt->bindParam(':font_code', $font_code);
		$stmt->execute();
		return $stmt->fetchObject();
	}
}
