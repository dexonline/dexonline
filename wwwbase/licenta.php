<?php
require_once("../phplib/util.php");

$restrictedSources = Model::factory('Source')
  ->where('canDistribute', 0)
  ->where_not_equal('shortName', 'dexonline')
  ->order_by_asc('publisher')
  ->find_many();

SmartyWrap::assign('page_title', 'Licență');
SmartyWrap::assign('restrictedSources', $restrictedSources);
SmartyWrap::display('licenta.ihtml');

?>
