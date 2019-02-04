<?php

require_once '../../lib/Core.php';
User::mustHave(User::PRIV_EDIT);

$sourceId = Request::get('sourceId', 0);

if ($sourceId) {
  $abbrevs = Model::factory('Abbreviation')
                  ->where('sourceId', $sourceId)
                  ->order_by_asc('short')
                  ->find_many();
}

Smart::assign('sourceId', $sourceId);
Smart::assign('results', $abbrevs);
Smart::display('ajax/getAbbreviations.tpl');
