<?php
require_once '../../lib/Core.php'; 
ini_set('memory_limit', '256M');

$modelType = Request::get('modelType');
$modelNumber = Request::get('modelNumber');
$lexemes = Lexeme::loadByCanonicalModel($modelType, $modelNumber);

RecentLink::add("Lexeme pentru modelul: {$modelType}{$modelNumber}");

Smart::assign('lexemes', $lexemes);
Smart::assign('modelType', $modelType);
Smart::assign('modelNumber', $modelNumber);
Smart::addCss('admin');
Smart::display('admin/viewLexemesByModel.tpl');
