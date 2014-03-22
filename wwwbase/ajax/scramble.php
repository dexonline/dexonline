<?php
require_once("../../phplib/util.php");
setlocale(LC_ALL, "ro_RO.utf8");

$option = util_getRequestParameter('difficulty');

define('short', 6);
define('medium', 8);
define('long', 10);
define('verylong', 20);

switch ($option) {
case 4:
  $minLength = long;
  $maxLength = verylong;
  break;
case 3:
  $maxLength = long;
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

// echo $lexem->formUtf8General;
// echo $indexWords;
$result = array('noWords' => $indexWords, 'randomWord' => $lexem->formUtf8General);
echo json_encode($result);

?>