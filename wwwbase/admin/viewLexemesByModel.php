<?php
require_once("../../phplib/Core.php"); 
ini_set('memory_limit', '256M');

$modelType = Request::get('modelType');
$modelNumber = Request::get('modelNumber');
$lexems = Lexeme::loadByCanonicalModel($modelType, $modelNumber);

RecentLink::add("Lexeme pentru modelul: {$modelType}{$modelNumber}");

SmartyWrap::assign('lexems', $lexems);
SmartyWrap::assign('modelType', $modelType);
SmartyWrap::assign('modelNumber', $modelNumber);
SmartyWrap::addCss('admin');
SmartyWrap::display('admin/viewLexemesByModel.tpl');
