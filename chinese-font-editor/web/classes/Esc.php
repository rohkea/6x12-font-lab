<?php
namespace FontEditor;

/**
 * Helper class with common escape functions, to be used statically.
 */
class Esc {
	/**
	 * Escapes string for use in HTML tag's attribute values.
	 * @param string $s Unescaped string
	 * @return string String for use in HTML tag's attributes.
	 */
	static function attr($s) {
		return htmlspecialchars($s, ENT_HTML5);
	}

	/**
	 * Escapes string for use in HTML text nodes.
	 * @param string $s Unescaped string
	 * @return string String for use in HTML text nodes.
	 */
	static function text($s) {
		return htmlspecialchars($s, ENT_NOQUOTES | ENT_HTML5);
	}
}
