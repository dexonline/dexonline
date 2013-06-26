<?php
require_once("../../phplib/util.php");

$easyLexems = Model::factory('Lexem')
  ->raw_query('select l.* from Lexem l, LexemDefinitionMap ldm, Definition d ' .
              'where l.id = ldm.lexemId and ldm.definitionId = d.id and d.status = 0 ' .
              'group by l.id having sum(char_length(internalRep)) <= 100 limit 10')
  ->find_many();

SmartyWrap::assign('easyLexems', $easyLexems);
SmartyWrap::assign('sectionTitle', 'Structurare definiții');
SmartyWrap::addCss('select2');
SmartyWrap::addJs('jquery', 'select2', 'struct');
SmartyWrap::displayAdminPage('struct/index.ihtml');

?>
