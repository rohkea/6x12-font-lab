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
}
