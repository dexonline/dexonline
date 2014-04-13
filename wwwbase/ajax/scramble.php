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
      ->where_gte('charLength', 5)
      ->where_lte('charLength', 5)
      ->count();

  $lexem = Model::factory('Lexem')
    ->where_gte('charLength', 5)
    ->where_lte('charLength', 5)
    ->offset(rand(0, $indexWords - 1))
    ->find_one();
  $Found = Model::factory('Lexem')
    ->where_like('formUtf8General',$searchWord)
    ->find_one();
 //$maxStep = strlen($lexem);
  //$lexemArray = str_split($lexem);
  //print_r($lexemArray)
 // $wordArray = array();
//  print strlen($lexem);
//  for($i = 0 ;$i < $maxStep; $i++) {
//    for($j = 0;$j < $maxStep; $j++) {
//      $search = Model::factory('Lexem')
//        ->where('formUtf8General',)
//    }
//  }

/* function permute($items, $perms = array( )) {
    if (empty($items)) {
        $return = array($perms);
    }  else {
        $return = array();
        for ($i = count($items) - 1; $i >= 0; --$i) {
             $newitems = $items;
             $newperms = $perms;
         list($foo) = array_splice($newitems, $i, 1);
             array_unshift($newperms, $foo);
             $return = array_merge($return, pc_permute($newitems, $newperms));

         }
    }
    return $return;
}
print_r(permute($lexemArray)); */

//Conditional cases: 
  if($Found != null) {
    $Found = 1;
  } else {
    $Found = 0;
  }

$result = array('noWords' => $indexWords, 'randomWord' => $lexem->formUtf8General, 'Found' => $Found);
echo json_encode($result);
// echo $lexem->formUtf8General;
// echo $indexWords;

?>