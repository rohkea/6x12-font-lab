<?php
namespace FontEditor;

/**
 * Helper class for functions that don't fit in other helper classes.
 */
class Helper {
	/**
	 * Common function for displaying a non-recoverable error message, and stops
	 * the program execution.
	 * @param string $text Text to be displayed to the user
	 */
	static function die($string) {
		header('Content-Type: text/plain; Charset=UTF-8');
		echo $string;
		die;
	}

	/**
	 * Get page to which the user should be redirected after the glyph has
	 * been successfully saved.
	 * @param string $font Code of the font
	 * @param string $character_code Character code
	 * @return string Partial URL like index.php
	 */
	static function getRedirectForSavedChar($font, $character_code) {
		$fonts_data = [
			'tw' => ['charset' => 'BIG-5', 'listScript' => 'big5.php'],
			'cn' => ['charset' => 'CP936', 'listScript' => 'gbk.php'],
		];
		if (isset($fonts_data[$font])) {
			$font_data = $fonts_data[$font];
			$utf8_character = mb_chr($character_code, 'UTF-8');
			$big5_character = mb_convert_encoding($utf8_character, $font_data['charset'], 'UTF-8');
			$first_byte = ord($big5_character[0]);
			if ($first_byte >= 0xA1 && $first_byte <= 0xFE) {
				return $font_data['listScript'] . '?first_byte=' . $first_byte;
			}
		}

		return 'index.php';
	}
}
