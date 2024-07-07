<?php
namespace FontEditor;

/**
 * Base class for GBK and Big-5 encodings.
 */
class BaseLegacyEncoding {
	/**
	 * Legacy codepage used in mb_convert_encoding.
	 */
	protected static $codepage = 'UTF-8';

	/**
	 * the second byte that will be used in ranges to show the first
	 * character of the range.
	 */
	protected static $rangeStartSecondByte = 0x40;


	/**
	 * the second byte that will be used in ranges to show the last
	 * character of the range.
	 */
	protected static $rangeEndSecondByte = 0xFE;

	/**
	 * Makes a character based on first and second byte.
	 * @param int $first_byte First byte of the non-Unicode encoding.
	 * @param int $second_byte Second byte of the non-Unicode encoding.
	 * @return \stdClass with character and original_code
	 * properties.
	 */
	static function makeCharacter($first_byte, $second_byte) {
		$result = new \stdClass;
		$legacy_char = pack('C*', $first_byte, $second_byte);
		$result->character = mb_convert_encoding($legacy_char, 'UTF-8', static::$codepage);
		$unpacked_legacy_codepoint = unpack('n', $legacy_char);
		$result->original_code = '0x' . strtoupper(dechex($unpacked_legacy_codepoint[1]));
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
		$range->first = static::makeCharacter($first_byte, static::$rangeStartSecondByte);
		$range->last = static::makeCharacter($first_byte, static::$rangeEndSecondByte);
		return $range;
	}
}
