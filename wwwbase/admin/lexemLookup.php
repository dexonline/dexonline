<?php
require_once("../../phplib/util.php"); 

$lexemName = util_getRequestParameter('lexemName');

if (StringUtil::hasRegexp($lexemName)) {
  $hasDiacritics = StringUtil::hasDiacritics($lexemName);
  $lexems = Lexem::searchRegexp($lexemName, $hasDiacritics, null, true);
} else {
  $lexems = Lexem::loadByExtendedName($lexemName);
}

if (count($lexems) == 1) {
  util_redirect('lexemEdit.php?lexemId=' . $lexems[0]->id);
}

SmartyWrap::assign('lexems', $lexems);
SmartyWrap::assign('sectionTitle', "Căutare lexem: '$lexemName'");
SmartyWrap::assign('sectionCount', count($lexems));
SmartyWrap::assign('recentLinks', RecentLink::loadForUser());
SmartyWrap::displayAdminPage('admin/lexemList.ihtml');

?>
