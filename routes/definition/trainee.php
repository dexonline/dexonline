<?php
User::mustHave(User::PRIV_EDIT | User::PRIV_TRAINEE);

$defs = Model::factory('Definition')
  ->where('userId', User::getActiveId())
  ->order_by_desc('createDate')
  ->limit(500)
  ->find_many();

$sourceIds = array_unique(Util::objectProperty($defs, 'sourceId'));
if ($sourceIds) {
  $sources = Model::factory('Source')
    ->where_in('id', $sourceIds)
    ->find_many();
} else {
  $sources = [];
}
$sourceMap = Util::mapById($sources);

Smart::assign([
  'defs' => $defs,
  'sourceMap' => $sourceMap,
]);
Smart::addResources('tablesorter');
Smart::display('definition/trainee.tpl');
