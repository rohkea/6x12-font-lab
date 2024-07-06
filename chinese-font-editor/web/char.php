<?php
namespace FontEditor;
require_once "common.php";

/**
 * Page for editing a single character.
 */


if ($_POST['code']) {
	$character_code = intval($_POST['code']);
	$font_code = $_POST['font'];
	$font = FontModel::getByCode($font_code);
	if (!$font) Helper::die("Unknown font: $font_code.");

	$data = $_POST['data'];
	$encoded_data = GlyphModel::encodeBinary($data);

	$glyph = [
		'char_code' => $character_code,
		'font_id' => $font->id,
		'verified' => false,
		'is_active' => false,
		'is_fullwidth' => $encoded_data->is_fullwidth,
		'data' => $encoded_data->data
	];
	
	$insert_successful = GlyphModel::insert($glyph);
	if (!$insert_successful) {
		Helper::die('Could not insert the glyph.');
	}

	header('Location: ' . Helper::getRedirectForSavedChar($font->code, $character_code));
	die();
}

if (!isset($_GET['code'])) {
	Helper::die('No code argument passed. Character code is required.');
}

$character_code = $_GET['code'];
if (!intval($character_code)) {
	$character_code = mb_ord($character_code, 'UTF-8');
}
$character = mb_chr($character_code, 'UTF-8');
$font_code = $_GET['font'] ?? 'base';
$reference_character_code = $_GET['ref'] ?? $character_code;
if (!intval($reference_character_code)) {
	$reference_character_code = mb_ord($reference_character_code, 'UTF-8');
}
$reference_character = mb_chr($reference_character_code, 'UTF-8');

$font = FontModel::getByCode($font_code);
if (!$font) Helper::die("Unknown font: $font_code.");

$fallback_fonts = array_map(function ($font_code) {
	return FontModel::getByCode($font_code);
}, array_unique([$font_code, 'base', 'tw', 'hk', 'cn']));

$existing_character = GlyphModel::getInFont($font->id, $character_code);
if (!$existing_character) {
	foreach ($fallback_fonts as $fallback_font) {
		$fallback_character = GlyphModel::getInFont($fallback_font->id, $reference_character_code);
		if ($fallback_character) break;
	}
} else {
	$reference_character_code = 0;
	$reference_character = null;
}

$preset_data = GlyphModel::decodeBinary(
	$existing_character->data ?? $fallback_character->data ?? str_pad('', 24, "\x00"),
	$existing_character->is_fullwidth ?? $fallback_character->is_fullwidth ?? true
);

Templates::show('header');
Templates::show('char', [
	'character' => $character,
	'character_code' => $character_code,
	'reference_character_code' => $reference_character_code,
	'reference_character' => $reference_character,
	'font' => $font,
	'ascii_char_data' => $preset_data,
	'char_exists' => (bool) $existing_character,
]);
Templates::show('footer');
