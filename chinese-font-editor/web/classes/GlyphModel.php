<?php
namespace FontEditor;

/**
 * Static class for requesting and preprocessing glyph information.
 */
class GlyphModel {
	/**
	 * Retrieve the glyph information from a specific font.
	 * @param int $font_id ID for the font
	 * @param int $character_code Unicode codepoint of the character
	 * @return \stdClass | null
	 */
	public static function getInFont(int $font_id, int $character_code) {
		$db = DB::get();
		$stmt = $db->prepare('SELECT * FROM glyphs WHERE char_code = :char_code AND font_id = :font_id');
		$stmt->bindParam(':char_code', $character_code);
		$stmt->bindParam(':font_id', $font_id);
		$stmt->execute();
		return $stmt->fetchObject();
	}

	/**
	 * Decodes binary format of glyph into ASCII format (with `.` and
	 * `@` characters).
	 * @param string $binary_data Binary data
	 * @param bool $is_fullwidth Whether the character is fullwidth.
	 * @return string ASCII data
	 */
	public static function decodeBinary(string $binary_data, bool $is_fullwidth): string {
		$width = $is_fullwidth ? 12 : 6;
		$length = strlen($binary_data) / 2;
		$ints = unpack('S*', $binary_data);
		if (!$ints) {
			return '';
		}

		$lines = array_map(function ($int) use ($width) {
			return str_replace(['0', '1'], ['.', '@'], str_pad(strrev(decbin($int)), $width, '0'));
		}, $ints);
		return implode("\n", $lines);
	}

	/**
	 * Encodes binary format of glyph from ASCII format.
	 * @param string $ascii_data ASCII data with lines of `.` and `@`
	 * data.
	 * @return \stdClass stdClass with data and is_fullwidth.
	 */
	public static function encodeBinary(string $ascii_data) {
		$lines = explode("\n", trim($ascii_data));
		$width = max(array_map(function ($line) {
			return strlen($line);
		}, $lines));
		$height = count($lines);

		$integers = array_map(function ($line) {
			$string = preg_replace(
				'/[^0]/',
				'1',
				str_replace(
					'.',
					'0',
					strrev(trim($line))
				)
			);
			return bindec($string);
		}, $lines);

		$result = new \stdClass;
		$result->data = pack('S*', ...$integers);
		$result->is_fullwidth = $width > 6;

		return $result;
	}

	/**
	 * Inserts glyph into the database.
	 * @param array $glyph Associative array with same items as
	 * the database: `char_code`, `font_id`, `added_at` (Unix timestamp),
	 * `adder_ip`, `verified` (boolean), `is_active` (boolean),
	 * `is_fullwidth` (boolean), `data` (string, binary-encoded).
	 * @return bool Whether the insert was successful.
	 */
	public static function insert(array $glyph) {
		return DB::insert('glyphs', $glyph, [
			'id' => \PDO::PARAM_INT,
			'char_code' => \PDO::PARAM_INT,
			'font_id' => \PDO::PARAM_INT,
			'added_at' => \PDO::PARAM_INT,
			'adder_ip' => \PDO::PARAM_STR,
			'verified' => \PDO::PARAM_INT,
			'is_active' => \PDO::PARAM_INT,
			'is_fullwidth' => \PDO::PARAM_INT,
			'data' => \PDO::PARAM_STR
		], [
			'adder_ip' => $_SERVER['REMOTE_ADDR'],
			'added_at' => \time(),
		]);
	}
}
