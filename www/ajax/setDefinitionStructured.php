<?php

require_once '../../lib/Core.php';
User::mustHave(User::PRIV_EDIT);

$id = Request::get('id');
$value = Request::get('value');

Log::info("Setting structured = %d on definition %d", $value, $id);

$def = Definition::get_by_id($id);
$def->structured = $value;
$def->save();
