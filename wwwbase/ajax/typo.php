<?php

require_once("../../phplib/Core.php");
Util::assertNotMirror();

$text = Request::get('text');
$definitionId = Request::get('definitionId');

if ($text && $definitionId) {
  $typo = Model::factory('Typo')->create();
  $typo->definitionId = $definitionId;
  $typo->problem = $text;
  $typo->save();
}
