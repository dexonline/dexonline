<?php
require_once("../../phplib/util.php");
setlocale(LC_ALL, "ro_RO.utf8");
ini_set('memory_limit','128M');

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
    ->where('formUtf8General',$searchWord)
    ->find_one();

//Conditional cases: 
  if($Found) {
    $Found = 1;
  } else {
    $Found = 0;
  }

//for spliting into substrings a word of 5 letters
$wordList = array();
for($i = 0; $i < 3; $i++) {
  $subWord = mb_substr($lexem, $i, 3);
  $lexemArray = str_split_unicode($subWord);
  $temp = letterPermute($lexemArray);
  $wordList = array_merge($wordList, $temp);
}
for($i = 0; $i < 2; $i++) {
  $subWord = mb_substr($lexem, $i, 4);
  $lexemArray = str_split_unicode($subWord);
  $temp = letterPermute($lexemArray);
  $wordList = array_merge($wordList, $temp);
}
//first char + last 2 chars
$subWord = mb_substr($lexem, 0, 1); 
$subWord .= mb_substr($lexem, 3, 2);
$lexemArray = str_split_unicode($subWord);
$temp = letterPermute($lexemArray);
$wordList = array_merge($wordList, $temp);
//second char + last 2 chars
$subWord = mb_substr($lexem, 1, 1);
$subWord .= mb_substr($lexem, 3, 2);
$lexemArray = str_split_unicode($subWord);
$temp = letterPermute($lexemArray);
$wordList = array_merge($wordList, $temp);
//first char + last 3 chars
$subWord = mb_substr($lexem, 0, 1);
$subWord .= mb_substr($lexem, 2, 3);
$lexemArray = str_split_unicode($subWord);
$temp = letterPermute($lexemArray);
$wordList = array_merge($wordList, $temp);
//first 3 chars + last char
$subWord = mb_substr($lexem, 0, 3);
$subWord .= mb_substr($lexem, 4, 1);
$lexemArray = str_split_unicode($subWord);
$temp = letterPermute($lexemArray);
$wordList = array_merge($wordList, $temp);
//permute the whole word.
$lexemSplit = $lexem;
$lexemArray = str_split_unicode($lexemSplit);
$wordList = array_merge($wordList, letterPermute($lexemArray));
//echo '<pre>'; print_r($wordList); echo '</pre>';
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

function letterPermute($items, $perms = array( )) {
    if (empty($items)) {
        $return = array($perms);
    }  else {
        $return = array();
        for ($i = count($items) - 1; $i >= 0; --$i) {
            $newitems = $items;
            $newperms = $perms;
            list($foo) = array_splice($newitems, $i, 1);
            array_unshift($newperms, $foo);
            $return = array_merge($return, letterPermute($newitems, $newperms));
            }              
         }
    return $return;
}

$wordList1 = array();
$wordsFound = array('init'); //the final words that were found after doing all the possible combinations.
$j = 0;
$inArray = 0;
for($i = 0 ;$i < count($wordList); $i++) {
  $wordList1[$i] = implode($wordList[$i]);
  $search = Model::factory('Lexem')
          ->where("formUtf8General", $wordList1[$i])
          ->find_one();
  if($search) {
    for($k = 0; $k < count($wordsFound); $k++) {
      if($wordsFound[$k] == $search->formUtf8General) { //check if the word already exists in array, its possible due to the number or permutations.
        $inArray = 1;
        break;
        } 
        else {
         $inArray = 0;
      }
    }
    if(!$inArray){
      $wordsFound[$j] = $search->formUtf8General;
        $j++;
    }
  }
}
unset($wordList1);
unset($wordList);
//echo '<pre>'; print_r($wordsFound); echo '</pre>';
$result = array('noWords' => $indexWords, 'randomWord' => $lexem->formUtf8General, 'Found' => $Found, 'everyWord' => $wordsFound);
echo json_encode($result);
// echo $lexem->formUtf8General;
// echo $indexWords;

?>