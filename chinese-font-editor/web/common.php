<?php
/**
 * This file should be included in all user-accessible .php pages.
 */

include "config.php";

define('FONT_EDITOR', true);

spl_autoload_register(function ($full_class_name) {
	list($namespace, $class_name) = explode('\\', $full_class_name, 2);

	if ($namespace === 'FontEditor') {
		include "classes/$class_name.php";
	}
});

