<?php
namespace FontEditor;
require_once "common.php";

/**
 * Page for editing a single character.
 */


if ($_POST['submit']) {
	echo 'saving not yet supported';
	die();
}

if (!isset($_GET['code'])) {
	Helper::die('No code argument passed. Character code is required.');
}

$character_code = $_GET['code'];
$character = mb_chr($character_code);
$font_code = $_GET['font'] ?? 'base';

$font = FontModel::getByCode($font_code);
if (!$font) {
	Helper::die("Unknown font: $font_code.");
}

$fallback_fonts = array_map(function ($font_code) {
	return FontModel::getByCode($font_code);
}, ['base', 'tw', 'hk', 'cn']);

$existing_character = GlyphModel::getInFont($font->id, $character_code);
$fallback_character = $existing_character;
if (!$existing_character) {
	foreach ($fallback_fonts as $fallback_font) {
		$fallback_character = GlyphModel::getInFont($fallback_font->id, $character_code);
		if ($fallback_font) break;
	}
}
$preset_data = '';
if ($fallback_character) {
	$preset_data = GlyphModel::decodeBinary($fallback_character->data, $fallback_character->is_fullwidth);
}

Templates::show('header');
Templates::show('char', [
	'character' => $character,
	'character_code' => $character_code,
	'font' => $font,
	'ascii_char_data' => $preset_data,
	'char_exists' => (bool) $existing_character,
]);
Templates::show('footer');
