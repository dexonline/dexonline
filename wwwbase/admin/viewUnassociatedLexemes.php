<?php
require_once '../../phplib/Core.php'; 
User::mustHave(User::PRIV_EDIT);
Util::assertNotMirror();

$lexemes = Lexeme::getUnassociated();

SmartyWrap::assign('lexemes', $lexemes);
SmartyWrap::addCss('admin');
SmartyWrap::display('admin/viewUnassociatedLexemes.tpl');
