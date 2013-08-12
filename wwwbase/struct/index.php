<?php
require_once("../../phplib/util.php");

// Select the first 500 lexems with short definitions and present 20 of them at random.
// It's hard to select all the easy lexems because the query is very slow.
define('NUM_EASY_LEXEMS', 500);
define('NUM_EASY_LEXEMS_SHOWN', 20);

$easyLexems = Model::factory('Lexem')
  ->raw_query('select l.* from Lexem l, LexemDefinitionMap ldm, Definition d ' .
              'where l.id = ldm.lexemId and ldm.definitionId = d.id and d.status = 0 ' .
              'group by l.id having sum(char_length(internalRep)) <= 100 limit ' . NUM_EASY_LEXEMS)
  ->find_many();

// Now select NUM_EASY_LEXEMS_SHOWN of them at random
for ($i = 0; $i < NUM_EASY_LEXEMS_SHOWN; $i++) {
  $j = rand($i, NUM_EASY_LEXEMS - 1);
  $tmp = $easyLexems[$i];
  $easyLexems[$i] = $easyLexems[$j];
  $easyLexems[$j] = $tmp;
}
array_splice($easyLexems, NUM_EASY_LEXEMS_SHOWN);

SmartyWrap::assign('easyLexems', $easyLexems);
SmartyWrap::assign('sectionTitle', 'Structurare definiții');
SmartyWrap::addCss('select2');
SmartyWrap::addJs('jquery', 'select2', 'struct');
SmartyWrap::displayAdminPage('struct/index.ihtml');

?>
