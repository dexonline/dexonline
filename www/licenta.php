<?php
require_once '../lib/Core.php';

$restrictedSources = Model::factory('Source')
  ->where('canDistribute', 0)
  ->where_not_equal('shortName', 'dexonline')
  ->order_by_asc('publisher')
  ->find_many();

Smart::assign('restrictedSources', $restrictedSources);
Smart::display('licenta.tpl');
