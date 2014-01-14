<?php
require_once("../../phplib/util.php"); 
util_assertModerator(PRIV_EDIT);
util_assertNotMirror();

$form = util_getRequestParameter('form');
$structStatus = util_getRequestParameter('structStatus');
$searchButton = util_getRequestParameter('searchButton');

if (!$searchButton) {
  util_redirect('index.php');
}

$form = StringUtil::cleanupQuery($form);
$arr = StringUtil::analyzeQuery($form);
$hasDiacritics = $arr[0];
$field = $hasDiacritics ? 'formNoAccent' : 'formUtf8General';
$regexp = StringUtil::dexRegexpToMysqlRegexp($form);

$query = "select * from Lexem where $field $regexp";
if ($structStatus) {
  $query .= " and structStatus = {$structStatus}";
}
$query .= ' order by formNoAccent limit 500';
$lexems = Model::factory('Lexem')->raw_query($query, null)->find_many();

SmartyWrap::assign('lexems', $lexems);
SmartyWrap::assign('sectionTitle', 'Căutare lexeme');
SmartyWrap::assign('sectionCount', count($lexems));
SmartyWrap::assign('recentLinks', RecentLink::loadForUser());
SmartyWrap::displayAdminPage('admin/lexemList.ihtml');

?>
