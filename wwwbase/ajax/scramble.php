<?php
require_once("../../phplib/util.php");
setlocale(LC_ALL, "ro_RO.utf8");
ini_set('memory_limit','-1');


//$searchWord = util_getRequestParameter('searchWord');
//$option = $_GET['difficulty'];
//var_dump($option);

define("NIVEL_FOARTE_USOR", 4);
define("NIVEL_USOR", 5);
define("NIVEL_NORMAL", 6);
define("NIVEL_GREU", 7);
define("NIVEL_DICTIONAR", 8);

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

function randomWordGenerator() {

  $dbSearch = 0;
  $level = 0;
  $option = util_getRequestParameter('difficulty');

  switch ($option)
  {
    case "1": $level = NIVEL_FOARTE_USOR;  break;
    case "2": $level = NIVEL_USOR;         break;
    case "3": $level = NIVEL_NORMAL;       break;
    case "4": $level = NIVEL_GREU;         break;
    case "5": $level = NIVEL_DICTIONAR;    break;
    default: $level = 4;
  }

  //var_dump($level);

  $indexWords = Model::factory('Lexem')
    ->where('charLength', $level)
    ->count();

  $dbSearch = Model::factory('Lexem')
    ->where('charLength', $level)
    ->offset(rand(0, $indexWords - 1))
    ->find_one();

   $result = $dbSearch->formUtf8General;
   return $result;
}

function splitWord(){
  $lexem = 0;
  $lexem = randomWordGenerator();
  //for spliting into substrings a word of 5 letters
  $wordList = array();
  for ($i = 0; $i < 3; $i++) {
      $subWord = mb_substr($lexem, $i, 3);
      $lexemArray = str_split_unicode($subWord);
      $temp = letterPermute($lexemArray);
      $wordList = array_merge($wordList, $temp);
    }
//echo '<pre>'; print_r($wordList); echo '</pre>';
  for ($i = 0; $i < 2; $i++) {
    $subWord = mb_substr($lexem, $i, 4);
    $lexemArray = str_split_unicode($subWord);
    $temp = letterPermute($lexemArray);
    $wordList = array_merge($wordList, $temp);
  }
//first char + last 2 chars
  for($i = 0; $i < 2; $i++) {
    $subWord = mb_substr($lexem, $i, 1); 
    $subWord .= mb_substr($lexem, 3, 2);
    $lexemArray = str_split_unicode($subWord);
    $temp = letterPermute($lexemArray);
    $wordList = array_merge($wordList, $temp);
  }
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

  $wordArray = findWords($wordList);
  ajaxEcho($lexem,$wordArray);

}

function findWords($wordList) {
  $wordList1 = array();
  $wordsFound = array(); //the final words that were found after doing all the possible combinations.
  $j = 0;
  for($i = 0 ;$i < count($wordList); $i++) {
    $wordList1[$i] = implode($wordList[$i]);
    $search = Model::factory('Lexem')
            ->where("formUtf8General", $wordList1[$i])
            ->find_one();
    if($search && $search == $wordList1[$i]) {
      if(!in_array($search->formUtf8General, $wordsFound)) {
        $wordsFound[$j] = $search->formUtf8General;
        $j++;
      }
    }
  }
  return $wordsFound;
}

function ajaxEcho($randWord,$wordsFound){

 // if(count($wordsFound) < 4) {
 //   splitWord();
 // }
 // else {
  $result = array('randomWord' => $randWord, 'everyWord' => $wordsFound);
  echo json_encode($result);  
 //  }
}

splitWord();
;
?>