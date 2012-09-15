<?php

class NGram extends BaseObject {

	public static $_table = 'NGram';
	public static $NGRAM_SIZE = 3;
	public static $MAX_MOVE = 2; // Maximum distance an n-gram is allowed to migrate
	public static $LENGTH_DIF = 2; // Maximum length difference between the searched word and the suggested one

	public static function padWord($s) {
		return str_repeat('#', self::$NGRAM_SIZE - 1) . $s . str_repeat('%', self::$NGRAM_SIZE - 1);
	}

	public static function searchNGram($cuv) {
		$leng = mb_strlen($cuv);
		$ngramList = array();
		$ext = self::padWord($cuv);
		for ($i = 0; $i < $leng + self::$NGRAM_SIZE - 1; $i++)
			array_push($ngramList, mb_substr($ext,$i,self::$NGRAM_SIZE));
		$i = 0;
		$hash = array();
		foreach($ngramList as $ngram) {
			$lexemIdList = db_getArray(
				sprintf("select lexemId from NGram where ngram = '%s' and pos between %d and %d",
					$ngram, $i - self::$MAX_MOVE, $i + self::$MAX_MOVE));
			$lexemIdList = array_unique($lexemIdList);
			foreach($lexemIdList as $lexemId) {
				if (!isset($hash[$lexemId]))
					$hash[$lexemId] = 1;
				else
					$hash[$lexemId]++;
			}
			$i++;
		}
		arsort($hash);
		$max = current($hash);
		$lexIds = array_keys($hash,$max);
		$finalResult = array();
		foreach ($lexIds as $id) {
			$result = Model::factory('Lexem')->where('id', $id)->where_gte('charLength', $leng - self::$LENGTH_DIF)->where_lte('charLength', $leng + self::$LENGTH_DIF)->find_one();
			if ($result)
				array_push($finalResult, $result);
		}
		return $finalResult;
	}
}

?>
