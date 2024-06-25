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
	 * Decodes binary format of glyph into ASCII format (with . and @).
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
}
