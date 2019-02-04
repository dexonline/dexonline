<?php
require_once '../phplib/Core.php';
User::mustHave(User::PRIV_WOTD);

$artists = Model::factory('WotdArtist')
  ->order_by_asc('name')
  ->find_many();

// Cannot delete artists with WotM credits
$wotmMap = [];
$wotmCredits = WotdArtist::getAllWotmCredits();
foreach ($wotmCredits as $rec) {
  $wotmMap[$rec['label']] = true;
}

// Cannot delete artists appearing in WotdAssignment
foreach ($artists as $a) {
  $count = Model::factory('WotdAssignment')
         ->where('artistId', $a->id)
         ->count();
  $hasWotm = isset($wotmMap[$a->label]);
  $a->canDelete = !$count && !$hasWotm;
}

SmartyWrap::assign('artists', $artists);
SmartyWrap::display('autori-imagini.tpl');
