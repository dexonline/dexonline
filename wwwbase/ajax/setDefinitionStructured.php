<?php

require_once("../../phplib/util.php");
util_assertModerator(PRIV_EDIT);

$id = Request::get('id');
$value = Request::isset('value');

$def = Definition::get_by_id($id);
$def->structured = $value;
$def->save();

?>
