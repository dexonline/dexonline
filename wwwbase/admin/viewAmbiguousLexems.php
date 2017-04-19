<?php
require_once("../../phplib/util.php"); 
User::require(User::PRIV_EDIT);
Util::assertNotMirror();

$lexems = Lexem::loadAmbiguous();

SmartyWrap::assign('lexems', $lexems);
SmartyWrap::addCss('admin');
SmartyWrap::display('admin/viewAmbiguousLexems.tpl');

?>
