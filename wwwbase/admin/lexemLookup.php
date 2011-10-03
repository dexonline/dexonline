<?php
require_once("../../phplib/util.php"); 

$lexemName = util_getRequestParameter('lexemName');

if (text_hasRegexp($lexemName)) {
  $hasDiacritics = text_hasDiacritics($lexemName);
  $lexems = Lexem::searchRegexp($lexemName, $hasDiacritics, null);
} else {
  $lexems = Lexem::loadByExtendedName($lexemName);
}

if (count($lexems) == 1) {
  util_redirect('lexemEdit.php?lexemId=' . $lexems[0]->id);
}

smarty_assign('lexems', $lexems);
smarty_assign('sectionTitle', "Căutare lexem: '$lexemName'");
smarty_assign('recentLinks', RecentLink::loadForUser());
smarty_displayWithoutSkin('admin/lexemList.ihtml');

?>
