<?php
namespace FontEditor;
require_once "common.php";

/**
 * Page for displaying a list of GBK characters.
 */

// TODO prevent code duplication with big5.php

$first_byte = $_GET['first_byte'] ?? null;
if (!$first_byte) {
	$ranges = [];

	for ($i = 0x81; $i <= 0xFE; $i++) {
		$ranges[] = GBK::makeRange($i);
	}

	Templates::show('header');
	Templates::show('charset-overview', [
		'encoding_name' => 'GBK',
		'ranges' => $ranges,
	]);
	Templates::show('footer');
} else {
	$characters = [];
	for ($i = 0x40; $i <= 0xFE; $i++) {
		if ($i === 0x7F) continue;
		$characters[] = GBK::makeCharacter($first_byte, $i);
	}
	// TODO get next range, get previous range
	
	Templates::show('header');
	Templates::show('charset-range', [
		'characters' => $characters,
		'font_code' => 'cn',
	]);
	Templates::show('footer');
}
