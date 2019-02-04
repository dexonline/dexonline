<?php
require_once '../../phplib/Core.php'; 
ini_set('memory_limit', '256M');

$modelType = Request::get('modelType');
$modelNumber = Request::get('modelNumber');
$lexemes = Lexeme::loadByCanonicalModel($modelType, $modelNumber);

RecentLink::add("Lexeme pentru modelul: {$modelType}{$modelNumber}");

SmartyWrap::assign('lexemes', $lexemes);
SmartyWrap::assign('modelType', $modelType);
SmartyWrap::assign('modelNumber', $modelNumber);
SmartyWrap::addCss('admin');
SmartyWrap::display('admin/viewLexemesByModel.tpl');
