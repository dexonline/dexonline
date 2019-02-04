<?php
require_once '../phplib/Core.php';

$form = Request::get('form');
$version = Request::get('version', Config::DEFAULT_LOC_VERSION);
$ajax = Request::get('ajax');

if ($form) {
  $answer = Loc::lookup($form, $version);
  $class = 'has-feedback ' . ($answer ? 'has-success' : 'has-error');

  SmartyWrap::assign([
    'answer' => $answer,
    'class' => $class,
  ]);
}

SmartyWrap::assign([
  'form' => $form,
  'version' => $version,
  'versions' => getLocVersions(),
  'canonicalModelTypes' => ModelType::loadCanonical(),
]);

if ($ajax) {
  $results = [
    'answer' => $answer,
    'template' => SmartyWrap::fetch('bits/scrabbleResults.tpl'),
  ];
  header("Content-Type: application/json");
  print json_encode($results);
} else {
  SmartyWrap::display('scrabble.tpl');
}

/*************************************************************************/

function getLocVersions() {
  $result = [];
  foreach (Config::LOC_VERSIONS as $name => $date) {
    $lv = new LocVersion();
    $lv->name = $name;
    $lv->freezeTimestamp = strtotime($date);
    $result[] = $lv;
  }
  return $result;
}
