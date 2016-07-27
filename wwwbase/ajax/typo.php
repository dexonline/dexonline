<?php

require_once("../../phplib/util.php");
util_assertNotMirror();

$text = util_getRequestParameter('text');
$definitionId = util_getRequestIntParameter('definitionId');

if ($text && $definitionId) {
  $typo = Model::factory('Typo')->create();
  $typo->definitionId = $definitionId;
  $typo->problem = $text;
  $typo->save();
}
