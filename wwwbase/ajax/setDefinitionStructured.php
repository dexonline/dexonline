<?php

require_once("../../phplib/Core.php");
User::require(User::PRIV_EDIT);

$id = Request::get('id');
$value = Request::get('value');

Log::info("Setting structured = %d on definition %d", $value, $id);

$def = Definition::get_by_id($id);
$def->structured = $value;
$def->save();

?>
