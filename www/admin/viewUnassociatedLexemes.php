<?php
require_once '../../lib/Core.php'; 
User::mustHave(User::PRIV_EDIT);

$lexemes = Lexeme::getUnassociated();

SmartyWrap::assign('lexemes', $lexemes);
SmartyWrap::addCss('admin');
SmartyWrap::display('admin/viewUnassociatedLexemes.tpl');
