<?php
require_once("../phplib/Core.php");

$restrictedSources = Model::factory('Source')
  ->where('canDistribute', 0)
  ->where_not_equal('shortName', 'dexonline')
  ->order_by_asc('publisher')
  ->find_many();

SmartyWrap::assign('restrictedSources', $restrictedSources);
SmartyWrap::display('licenta.tpl');

?>
