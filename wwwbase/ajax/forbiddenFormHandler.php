<?php
require_once("../../phplib/util.php");
util_assertModerator(PRIV_EDIT);
error_log($_SERVER['REQUEST_URI']);

$id = util_getRequestParameter('id');
$action = util_getRequestParameter('action');

$if = InflectedForm::get_by_id($id);
if (!$if || ($action != 'deny' && $action != 'allow')) {
  http_response_code(404);
  exit;
}

$ff = ForbiddenForm::get_by_lexemModelId_inflectionId_variant(
  $if->lexemModelId, $if->inflectionId, $if->variant);
if ($action == 'deny') {
  if (!$ff) {
    $ff = ForbiddenForm::create($if);
    $ff->save();
  }
} else { // $action == 'allow'
  if ($ff) {
    $ff->delete();
  }
}
