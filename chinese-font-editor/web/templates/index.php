<?php
namespace FontEditor;
if (!defined('FONT_EDITOR')) die();
?>
<h1>Font editor for EasyRPG's Chinese font</h1>

<p>The goal of this project is to create a 12x12 Chinese fonts that will be
compatible with EasyRPG's Japanese font, Shinonome.</p>

<p>TODO: the editor is not yet working. It will, soon. Hopefully.</p>

<p>The goal of the project is to create two fonts:</p>

<ul>
	<li><code>tw</code> — a Traditional Chinese font following Taiwanese
standards covering Big5 and HKSCS character sets: see <a href="bug5.php">list
of all Big5 chatacters</a>,
	<li><code>cn</code> — a Simplified Chinese font following Mainland
standards covering the GB&nbsp;18030 character set.</li>
</ul>

<p>It's possible and desirable to add Unicode characters outside of these
character sets, too. However, Big5 and GB&nbsp;18030 character sets are the
priority.</p>

<p>The editor will present you Shinonome characters as starting point, if they
exist. Please note that these are not necessarily same as Chinese characters,
due to CJK unification. <strong>Please do not save the proposed character if
it doesn't look same in the relevant standard.</strong></p>



