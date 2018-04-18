<?php
require_once("../phplib/Core.php");
$form = Request::get('form');
$ajax = Request::get('ajax');
$locVersion = Request::get('locVersion');

$locVersions = Config::getLocVersions();
if (!User::can(User::PRIV_LOC)) {
  $locVersions = array_slice($locVersions, 1); // remove the version in progress
}

if ($locVersion && $form) {
  LocVersion::changeDatabase($locVersion);
  $form = Str::cleanupQuery($form);
  $field = Str::hasDiacritics($form) ? 'formNoAccent' : 'formUtf8General';
  $data = Model::factory('InflectedForm')
    ->table_alias('I')
    ->select('I.form', 'inflectedForm')
    ->select('L.formNoAccent', 'lexemeFormNoAccent')
    ->select('L.form', 'lexemeForm')
    ->select('L.modelType')
    ->select('L.modelNumber')
    ->select('L.restriction')
    ->select('Infl.description', 'inflection')
    ->join('Lexeme', 'I.lexemeId = L.id', 'L')
    ->join('ModelType', 'L.modelType = MT.code', 'MT')
    ->join('Model', 'MT.canonical = M.modelType and L.modelNumber = M.number', 'M')
    ->join('ModelDescription', 'M.id = MD.modelId and I.variant = MD.variant and I.inflectionId = MD.inflectionId', 'MD')
    ->join('Inflection', 'I.inflectionId = Infl.id', 'Infl')
    ->where('MD.applOrder', 0)
    ->where("I.{$field}", $form)
    ->where('L.isLoc', 1)
    ->where('MD.isLoc', 1)
    ->order_by_asc('L.formNoAccent')
    ->find_array();
  DB::changeDatabase(DB::$database);

  SmartyWrap::assign('form', $form);
  SmartyWrap::assign('selectedLocVersion', $locVersion);
  SmartyWrap::assign('data', $data);
} else {
  SmartyWrap::assign('selectedLocVersion', $locVersions[0]->name);
}

SmartyWrap::addJs('modelDropdown');
SmartyWrap::assign('locVersions', $locVersions);

if($ajax){
  $results = [
    'count' => count($data),
    'template' => SmartyWrap::fetch('scrabble-results.tpl'),
  ];
  header("Content-Type: application/json");
  print json_encode($results);
} else {
  SmartyWrap::display('scrabble.tpl');
}
