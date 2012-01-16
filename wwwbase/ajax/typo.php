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
  smarty_displayWithoutSkin('common/bits/typoConfirmation.ihtml');
} else {
  smarty_assign('definitionId', $definitionId);
  smarty_displayWithoutSkin('common/bits/typoForm.ihtml');
}

?>
