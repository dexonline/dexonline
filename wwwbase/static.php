<?php
require_once("../phplib/util.php");

$content = util_getRequestParameter('c');
switch ($content) {
  case 'abrev': $title = 'Abrevieri'; break;
  case 'adminHelp': $title = 'Mini-manual de moderare'; break;
  case 'format': $title = 'Standarde de formatare'; break;
  case 'loc': $title = 'Legendă LOC'; break;
  default: $title = 'Informații';
}

smarty_assign('title', "$title | DEX online");
smarty_assign('content', $content);
smarty_register_outputfilters();
$GLOBALS['smarty_theSmarty']->display('common/static.ihtml');
?>
