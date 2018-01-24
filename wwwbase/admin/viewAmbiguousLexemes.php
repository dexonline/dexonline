<?php
require_once("../../phplib/Core.php"); 
User::mustHave(User::PRIV_EDIT);
Util::assertNotMirror();

$lexems = Lexeme::loadAmbiguous();

SmartyWrap::assign('lexems', $lexems);
SmartyWrap::addCss('admin');
SmartyWrap::display('admin/viewAmbiguousLexems.tpl');
