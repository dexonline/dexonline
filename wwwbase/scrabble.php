<?php
require_once '../phplib/Core.php';

$form = Request::get('form');
$version = Request::get('version', Config::get('global.defaultLocVersion'));
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
  'versions' => Config::getLocVersions(),
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
