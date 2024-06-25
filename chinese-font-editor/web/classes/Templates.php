<?php
namespace FontEditor;

/**
 * Helper function for loading templates.
 */
class Templates {
	/**
	 * Display a template.
	 * @param string $template_name Template filename without the .php extension
	 * @param array parameters Associative array of template parameters,
	 * accessible inside the template as variables.
	 * @return void
	 */
	static function show($template_name, $parameters = []) {
		extract($parameters);
		include "templates/$template_name.php";
	}

	/**
	 * Loads the template as string, via output buffering.
	 * @param string $template_name Template filename without the .php extension
	 * @param array parameters Associative array of template parameters,
	 * accessible inside the template as variables.
	 * @return string The processed template file
	 */
	static function load($template_name, $parameters = []) {
		ob_start();
		static::show($template_name, $parameters);
		$result = ob_get_contents();
		ob_end_clean();
		return $result;
	}
}
