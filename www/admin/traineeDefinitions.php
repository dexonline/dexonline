<?php

require_once '../../lib/Core.php';
User::mustHave(User::PRIV_EDIT | User::PRIV_TRAINEE);

$defs = Model::factory('Definition')
  ->where('userId', User::getActiveId())
  ->order_by_desc('createDate')
  ->limit(500)
  ->find_many();

$sourceIds = array_unique(Util::objectProperty($defs, 'sourceId'));
$sources = Model::factory('Source')
  ->where_in('id', $sourceIds)
  ->find_many();
$sourceMap = Util::mapById($sources);

SmartyWrap::assign([
  'defs' => $defs,
  'sourceMap' => $sourceMap,
]);
SmartyWrap::addCss('tablesorter');
SmartyWrap::addJs('tablesorter');
SmartyWrap::display('traineeDefinitions.tpl');
