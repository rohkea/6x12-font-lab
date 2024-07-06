<?php
namespace FontEditor;
require_once "common.php";

/**
 * Page for displaying a list of Big5 characters.
 */


$first_byte = $_GET['first_byte'] ?? null;
if (!$first_byte) {
	$ranges = [];

	for ($i = 0xA1; $i <= 0xFE; $i++) {
		$ranges[] = Big5::makeRange($i);
	}

	Templates::show('header');
	Templates::show('charset-overview', [
		'encoding_name' => 'Big5',
		'ranges' => $ranges,
	]);
	Templates::show('footer');
} else {
	$characters = [];
	for ($i = 0x40; $i <= 0x7E; $i++) {
		$characters[] = Big5::makeCharacter($first_byte, $i);
	}
	for ($i = 0xA0; $i <= 0xFE; $i++) {
		$big5_char = pack('CC', $first_byte, $i);
		$characters[] = Big5::makeCharacter($first_byte, $i);
	}
	// TODO get next range, get previous range
	
	Templates::show('header');
	Templates::show('charset-range', [
		'characters' => $characters,
	]);
	Templates::show('footer');
}
