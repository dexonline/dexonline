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
  break;
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
  break;
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
  if($Found) {
    $Found = 1;
  } else {
    $Found = 0;
  }
$lexemSplit = $lexem;
$lexemArray = str_split_unicode($lexemSplit);

function str_split_unicode($str, $l = 0) {
    if ($l > 0) {
        $ret = array();
        $len = mb_strlen($str, "UTF-8");
        for ($i = 0; $i < $len; $i += $l) {
            $ret[] = mb_substr($str, $i, $l, "UTF-8");
        }
        return $ret;
    }
    return preg_split("//u", $str, -1, PREG_SPLIT_NO_EMPTY);
}

$wordList = pc_permute($lexemArray);

function pc_permute($items, $perms = array( )) {
    if (empty($items)) {
        $return = array($perms);
    }  else {
        $return = array();
        for ($i = count($items) - 1; $i >= 0; --$i) {
            $newitems = $items;
            $newperms = $perms;
            list($foo) = array_splice($newitems, $i, 1);
            array_unshift($newperms, $foo);
            $wordFound = Model::factory('Lexem')
              ->where('formUtf8General',implode($items))
              ->find_one();
            if($wordFound) {
              $return = array_merge($return, pc_permute($newitems, $newperms));
            } else {
            pc_permute($newitems, $newperms);
            }              
         }
    }
    return $return;
}
echo '<pre>'; print_r($wordList); echo '</pre>';
$result = array('noWords' => $indexWords, 'randomWord' => $lexem->formUtf8General, 'Found' => $Found);
echo json_encode($result);
// echo $lexem->formUtf8General;
// echo $indexWords;

?>