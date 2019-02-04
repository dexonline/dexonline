<?php
require_once '../../phplib/Core.php'; 
User::mustHave(User::PRIV_EDIT);

$lexemes = Lexeme::loadAmbiguous();

SmartyWrap::assign('lexemes', $lexemes);
SmartyWrap::addCss('admin');
SmartyWrap::display('admin/viewAmbiguousLexemes.tpl');
