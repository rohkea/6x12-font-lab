<?php
namespace FontEditor;
if (!defined('FONT_EDITOR')) die();
?>
<h1><?= $char_exists ? 'Editing' : 'Creating'; ?> character <?= Esc::text($character); ?> (<?= (int) $character_code; ?>) in <?= Esc::text($font->name); ?></h1>

<form method="post">
<textarea class="glyph-editor__textarea" data-glypheditor name="data"><?php
	echo Esc::text($ascii_char_data); 
?></textarea>

<div class="submit-button">
<input type="hidden" name="code" value="<?= Esc::attr($character_code); ?>">
<input type="hidden" name="font" value="<?= Esc::attr($font->code); ?>">
<input type="submit" name="save" value="<?= !$char_exists ? 'Add the new' : 'Submit the edited'; ?> glyph">
</div>
<script src="glypheditor.js"></script>
</form>
