<?php
namespace FontEditor;
if (!defined('FONT_EDITOR')) die();
?>
<!DOCTYPE HTML>
<html>
<head>
	<title><?= !empty($title) ? Esc::text($title) : 'Font editor'; ?></title>
	<?= !empty($additional_title_html) ? $additional_title_html : ''; ?>

	<link rel="stylesheet" href="style.css">
</head>
<body>
