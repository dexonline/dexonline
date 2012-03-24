<?php

require_once("../phplib/util.php");
setlocale(LC_ALL, "ro_RO.utf8");

define('limit_freq',1);
define('easy', 0.80);
define('normal', 0.60);
define('hard',0.40);
define('imp',0.20);

$min_freq = easy;
$max_freq = limit_freq;

$difficulty = (int) util_getRequestParameter('d');
if( is_int($difficulty)){
  switch ($difficulty) {
    case 1:
      $min_freq = normal;
      $max_freq = easy;
      break;
    case 2:
      $min_freq = hard;
      $max_freq = normal;
      break;
    default :
      if($difficulty >= 3) {
        $difficulty = 3;
        $min_freq = imp;
        $max_freq = hard;
	}
      break;
  }
}
else
  $difficulty = 0;


$query = sprintf("SELECT id FROM Lexem WHERE frequency BETWEEN %d AND %d AND length(formUtf8General) > 5 ORDER BY rand() LIMIT 1;",$min_freq, $max_freq);
$randLexemId =  db_getSingleValue($query);
$query = sprintf("SELECT lexicon, htmlRep FROM  Definition WHERE  id IN (SELECT definitionId FROM LexemDefinitionMap WHERE status=0 AND lexemId  = %d ) LIMIT 1;", $randLexemId);
$arr = db_getArrayOfRows($query);
$cuv = $arr[0]["lexicon"];
$definitie = $arr[0]["htmlRep"];

$nr_lit = mb_strlen($cuv);
$litere = array_filter(preg_split('//u',$cuv));
$iter = range(0,$nr_lit-1);
smarty_assign('iter', $iter);
smarty_assign('letters', preg_split('//u', 'aăâbcdefghiîjklmnopqrsștțuvwxyz-',NULL,PREG_SPLIT_NO_EMPTY));
smarty_assign('litere', $litere);
smarty_assign('page_title', 'Spânzurătoarea by CDL');
smarty_assign('cuvant', $cuv);
smarty_assign('nr_lit',$nr_lit);
smarty_assign('definitie', $definitie);
smarty_assign('difficulty', $difficulty);
smarty_displayCommonPageWithSkin("spnz.ihtml");
?>
