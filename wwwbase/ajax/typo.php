<?php
require_once("../../phplib/util.php");
util_assertNotMirror();

// Parse or initialize the GET/POST arguments
$text = util_getRequestParameter('text');
$definitionId = util_getRequestIntParameter('definitionId');
$submit = util_getRequestIntParameter('submit');

if ($submit) {
  if ($text && $definitionId) {
    $typo = Model::factory('Typo')->create();
    $typo->definitionId = $definitionId;
    $typo->problem = $text;
    $typo->save();
  }
  SmartyWrap::displayWithoutSkin('bits/typoConfirmation.ihtml');
} else {
  SmartyWrap::assign('definitionId', $definitionId);
  SmartyWrap::displayWithoutSkin('bits/typoForm.ihtml');
}

?>
