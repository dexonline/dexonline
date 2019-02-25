<?php
require_once '../../lib/Core.php';
User::mustHave(User::PRIV_EDIT);

$lexemes = Lexeme::getUnassociated();

Smart::assign('lexemes', $lexemes);
Smart::addResources('admin');
Smart::display('report/unassociatedLexemes.tpl');
