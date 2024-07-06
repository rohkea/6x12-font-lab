<?php
namespace FontEditor;
?>

<p>List of ranges of <?= Esc::text($encoding_name); ?> encoding:</p>

<ol>
<?php foreach ($ranges as $range): ?>
	<li><a href="big5.php?first_byte=<?= Esc::attr($range->first_byte); ?>">
		From
		[<code><?= Esc::text($range->first->original_code); ?></code>] <?= Esc::text($range->first->character); ?>
		to
		[<code><?= Esc::text($range->last->original_code); ?></code>] <?= Esc::text($range->last->character); ?>
	</a></li>
<?php endforeach; ?>
</ol>
