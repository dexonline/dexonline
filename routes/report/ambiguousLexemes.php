<?php

User::mustHave(User::PRIV_EDIT);

$lexemes = Lexeme::loadAmbiguous();

Smart::assign('lexemes', $lexemes);
Smart::addResources('admin');
Smart::display('report/ambiguousLexemes.tpl');
