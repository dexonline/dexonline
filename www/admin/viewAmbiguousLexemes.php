<?php
require_once '../../lib/Core.php'; 
User::mustHave(User::PRIV_EDIT);

$lexemes = Lexeme::loadAmbiguous();

SmartyWrap::assign('lexemes', $lexemes);
SmartyWrap::addCss('admin');
SmartyWrap::display('admin/viewAmbiguousLexemes.tpl');
