<?php
require_once("../../phplib/util.php");
setlocale(LC_ALL, "ro_RO.utf8");

$option = util_getRequestParameter('difficulty');
$searchWord = util_getRequestParameter('searchWord');

define('SHORT', 6);
define('MEDIUM', 8);
define('LONG', 10);
define('VERYLONG', 20);

switch ($option) {
case 5:
  $minLength = MEDIUM;
  $maxLength = VERYLONG;
case 4:
  $minLength = LONG;
  $maxLength = VERYLONG;
  break;
case 3:
  $maxLength = LONG;
  $minLength = MEDIUM;
  break;
case 2:
  $maxLength = MEDIUM;
  $minLength = SHORT;
  break;
case 1:
  $maxLength = SHORT;
  $minLength = SHORT;
  break;
default :
  $maxLength = SHORT;
  $minLength = SHORT;
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
  $Found = Model::factory('Lexem')
    ->where('formUtf8General',$searchWord)
    ->find_one();
//Conditional cases:
  if($Found != null) {
    $Found = 'Cuvantul exista';
  } else {
    $Found = 'Cuvantul nu exista';
  }

$result = array('noWords' => $indexWords, 'randomWord' => $lexem->formUtf8General, 'Found' => $Found);
echo json_encode($result);
// echo $lexem->formUtf8General;
// echo $indexWords;

?>