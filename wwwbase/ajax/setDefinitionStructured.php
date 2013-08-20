<?php

require_once("../../phplib/util.php");
util_assertModerator(PRIV_EDIT);

$id = util_getRequestParameter('id');
$value = util_getBoolean('value');

$def = Definition::get_by_id($id);
$def->structured = $value;
$def->save();

?>
