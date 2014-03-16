<?php
require_once("../../phplib/util.php");
setlocale(LC_ALL, "ro_RO.utf8");

<<<<<<< HEAD
define('short', 5);
define('medium', 8);
define('long', 15);
define('verylong', 30);

switch ($option) {
case 4:
=======
$option = util_getRequestParameter('option');

define('short', 6);
define('medium', 8);
define('long', 10);
define('verylong', 20);

switch ($option) {
case 4:
  $minLength = long;
>>>>>>> d846d1f19234ab2ce2e7d86f37a0c82415654260
  $maxLength = verylong;
  break;
case 3:
  $maxLength = long;
<<<<<<< HEAD
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
=======
  $minLength = medium;
  break;
case 2:
  $maxLength = medium;
  $minLength = short;
  break;
case 1:
  $maxLength = short;
  $minLength = short;
  break;
default :
  $maxLength = short;
  $minLength = short;
}

	$indexWords = Model::factory('Lexem')
			->where_gte('charLength', $minLength)
      ->where_lte('charLength', $maxLength)
			->count();
			
	$lexem = Model::factory('Lexem')
    ->where_gte('charLength', $minLength)
    ->where_lte('charLength', $maxLength)
    ->offset(rand(0, $indexWords - 1))
    ->find_one();

echo $lexem->formUtf8General;
echo $indexWords;
$result = array('noWords' => $indexWords, 'randomWord' => $lexem);
echo json_encode($lexem);

>>>>>>> d846d1f19234ab2ce2e7d86f37a0c82415654260
?>