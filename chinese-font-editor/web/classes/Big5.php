<?php
namespace FontEditor;

/**
 * Functions for working with the Big5 encoding.
 */
class Big5 {
	/**
	 * Makes a character based on first and second byte.
	 * @param int $first_byte First byte of the Big5 encoding.
	 * @param int $second_byte Second byte of the Big5 encoding.
	 * @return \stdClass with character and original_code
	 * properties.
	 */
	static function makeCharacter($first_byte, $second_byte) {
		$result = new \stdClass;
		$big5_char = pack('C*', $first_byte, $second_byte);
		$result->character = mb_convert_encoding($big5_char, 'utf-8', 'big-5');
		$unpacked_big5_codepoint = unpack('n', $big5_char);
		$result->original_code = '0x' . strtoupper(dechex($unpacked_big5_codepoint[1]));
		return $result;
	}

	/**
	 * Makes a range object for displaying a character range.
	 * @param int $first_byte First byte of the range.
	 * @return \stdClass Object with first, last and first_byte
	 * properties.
	 */
	static function makeRange($first_byte) {
		$range = new \stdClass;
		$range->first_byte = $first_byte;
		$range->first = Big5::makeCharacter($first_byte, 0x40);
		$range->last = Big5::makeCharacter($first_byte, 0xFE);
		return $range;
	}
}
