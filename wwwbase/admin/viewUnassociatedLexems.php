<?php
require_once("../../phplib/Core.php"); 
User::mustHave(User::PRIV_EDIT);
Util::assertNotMirror();

$lexems = Model::factory('Lexem')
        ->table_alias('l')
        ->select('l.*')
        ->left_outer_join('EntryLexem', ['l.id', '=', 'el.lexemId'], 'el')
        ->where_null('el.id')
        ->order_by_asc('l.form')
        ->find_many();

SmartyWrap::assign('lexems', $lexems);
SmartyWrap::addCss('admin');
SmartyWrap::display('admin/viewUnassociatedLexems.tpl');

?>
