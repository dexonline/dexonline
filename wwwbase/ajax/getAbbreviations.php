<?php

require_once("../../phplib/Core.php");
User::mustHave(User::PRIV_EDIT);
Util::assertNotMirror();

$sourceId = Request::get('sourceId', 0);

if ($sourceId) {
  $abbrevs = Model::factory('Abbreviation')
                  ->where('sourceId', $sourceId)
                  ->order_by_asc('short')
                  ->find_many();
}

SmartyWrap::assign('sourceId', $sourceId);
SmartyWrap::assign('results', $abbrevs);
SmartyWrap::assign('modUser', User::getActive());
SmartyWrap::display('ajax/getAbbreviations.tpl');
