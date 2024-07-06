<?php
namespace FontEditor;
?>
Characters in this range:

<?php foreach ($characters as $character): ?>
	<a href="char.php?code=<?= Esc::attr($character->character); ?>&font=tw">[<code><?= Esc::text($character->original_code); ?></code>] <?= Esc::text($character->character); ?></a>
<?php endforeach; ?>
