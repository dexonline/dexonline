<?php
ini_set('memory_limit', '256M');

$modelType = Request::get('modelType');
$modelNumber = Request::get('modelNumber');
$lexemes = Lexeme::loadByCanonicalModel($modelType, $modelNumber);

RecentLink::add("Lexeme pentru modelul: {$modelType}{$modelNumber}");

Smart::assign([
  'lexemes' => $lexemes,
  'modelType' => $modelType,
  'modelNumber' => $modelNumber,
]);
Smart::addResources('admin');
Smart::display('model/listLexemes.tpl');
