<?php
require_once("../phplib/util.php");

$content = util_getRequestParameter('c');
switch ($content) {
  case 'abrev': $title = 'Abrevieri'; break;
  case 'adminHelp': $title = 'Mini-manual de moderare'; break;
  case 'loc': $title = 'Legendă LOC'; break;
  default: exit;
}

smarty_assign('title', "$title | DEX online");
smarty_assign('content', $content);
smarty_register_outputfilters();
smarty_addCss('zepu');
print smarty_fetch('common/static.ihtml');
?>
