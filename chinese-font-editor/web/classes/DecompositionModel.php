<?php
namespace FontEditor;

/**
 * Static class for requesting font information.
 */
class DecompositionModel {
	/**
	 * Retrieve the data about a specific characters' decomposition.
	 * @param int $char_code Short code of the font
	 * @param bool $hydrate Whether it's necessary to fetch first_similar and second_similar
	 * @return \stdClass
	 */
	public static function getByCode(int $char_code, bool $hydrate = true) {
		// TODO: find the correct locale version, not first version
		$db = DB::get();
		$stmt = $db->prepare("
			SELECT * FROM decompositions
			WHERE char_code = :char_code
				-- ignore useless decompositions (我 = 我)
				AND NOT (type = '=' AND first_code = :char_code)
		");
		$stmt->bindParam(':char_code', $char_code);
		$stmt->execute();
		$result = $stmt->fetchObject();

		if ($hydrate) {
			$result->first_similar = self::getSimilarItems($result, true);
			$result->second_similar = self::getSimilarItems($result, false);
		}
		return $result;
	}

	/**
	 * Retrieves items with similar items.
	 * @param object $decomposition Decomposition model
	 * @param bool $is_first Whether to return decomposition for first_code
	 * @param int [$limit] Maximum number of items
	 * @return int[] Return array of character codes with similar decompositions
	 */
	public static function getSimilarItems(object $decomposition, bool $is_first, int $limit = 10) {
		$db = DB::get();
		$part_column = $is_first ? 'first_code' : 'second_code';
		$stmt = $db->prepare("
			SELECT DISTINCT d.char_code FROM decompositions AS d
				LEFT JOIN glyphs AS g ON d.char_code = g.char_code
			WHERE d.type = :type
				AND d.$part_column = :part_code
				AND g.id IS NOT NULL
				AND d.char_code <> :own_code
			GROUP BY d.char_code
			LIMIT :limit
		");

		$stmt->bindParam(':type', $decomposition->type);
		$part_code = $is_first ? $decomposition->first_code : $decomposition->second_code;
		if (!$part_code) return [];
		$stmt->bindParam(':part_code', $part_code);
		$stmt->bindParam(':own_code', $decomposition->char_code);
		$stmt->bindParam(':limit', $limit);
		$stmt->execute();
		$values = $stmt->fetchAll(\PDO::FETCH_COLUMN);
		if (empty($values)) $values = [];
		array_push($values, $part_code);

		return $values;
	}

	/**
	 *
	 */
	public static function getCharsWithSameParts() {
		
	}
}
