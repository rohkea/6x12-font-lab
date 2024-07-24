<?php
namespace FontEditor;
if (!defined('FONT_EDITOR')) die();
?>
<h1><?= $char_exists ? 'Editing' : 'Creating'; ?> character <?= Esc::text($character); ?> (<?= (int) $character_code; ?>) in <?= Esc::text($font->name); ?></h1>

<?php if (!$char_exists): ?>
<form method="get">
	<input type="hidden" name="code" value="<?= Esc::attr($character_code); ?>">
	<input type="hidden" name="font" value="<?= Esc::attr($font->code); ?>">
	<div class="reference-selector">
		<label>
			Start with reference:
			<input type="text" name="ref" value="">
			<input type="submit" value="Use reference">
		</label>
	</div>

	<?php if ($decomposition): ?>
	<p>Decomposition type <?= Esc::text($decomposition->type); ?>:</p>
	<ul>
		<?php foreach (['first_similar', 'second_similar'] as $similar_property): ?>
			<?php if(!empty($decomposition->$similar_property)): ?>
			<li>
				<?php foreach ($decomposition->$similar_property as $similar_code): ?>
					<!-- TODO: clean up this code -->
					<a class="reference-link" href="?code=<?php
						echo Esc::attr($character);
					?>&font=<?php
						echo Esc::attr($font->code);
					?>&ref=<?php
						echo Esc::attr(Esc::text(mb_chr($similar_code, 'UTF-8')));
					?>">
						<?= Esc::text(mb_chr($similar_code, 'UTF-8')); ?>
					</a>
				<?php endforeach; ?>
			</li>
			<?php endif; ?>
		<?php endforeach; ?>
	</ul>
	<?php endif; ?>
</form>
<?php endif; ?>
<form method="post">
<textarea class="glyph-editor__textarea" data-glypheditor name="data"><?php
	echo Esc::text($ascii_char_data); 
?></textarea>


<div class="submit-button">
<input type="hidden" name="code" value="<?= Esc::attr($character_code); ?>">
<input type="hidden" name="font" value="<?= Esc::attr($font->code); ?>">
<p>By submitting the glyph here you agree to place it into public domain under
the <a href="https://creativecommons.org/public-domain/cc0/">Creative Commons Zero</a>
public domain dedication.</p>
<input type="submit" name="save" value="<?= !$char_exists ? 'Add the new' : 'Submit the edited'; ?> glyph (as public domain / CC0)">
</div>
<script src="glypheditor.js"></script>
</form>
