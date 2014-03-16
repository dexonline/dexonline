<?php
require_once("../../phplib/util.php");
setlocale(LC_ALL, "ro_RO.utf8");

define('short', 5);
define('medium', 8);
define('long', 15);
define('verylong', 30);

switch ($option) {
case 4:
  $maxLength = verylong;
  break;
case 3:
  $maxLength = long;
  break;
case 2:
  $maxLength = medium;
  break;
case 1:
  $maxLength = short;
  break;
default :
  $maxLength = easyLength;
}

	$indexWords = Model::factory('Lexem')
			->where_raw('charLength =>5')
			->where_raw('charLength =<'.$maxLength)
			->count();
			
	$lexem = Model::factory('Lexem')
    ->where_raw('char_length(formUtf8General) >= 5')
    ->where_raw('char_length(formUtf8General) <= '.$maxLength)
    ->offset(rand(0, $indexWords - 1))
    ->find_one();
}
?>