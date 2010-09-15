<?php
require_once("../phplib/util.php");
$p = util_getRequestParameter('p');

switch($p) {
  case '404': smarty_assign('page_title', 'Pagină inexistentă'); break;
  case 'faq': smarty_assign('page_title', 'Informații'); break;
  case 'contact': smarty_assign('page_title', 'Contact'); break;
  case 'license': smarty_assign('page_title', 'Licență Publică Generală GNU'); break;
  case 'links': smarty_assign('page_title', 'Legături'); break;
  case 'ads': smarty_assign('page_title', 'Publicitate pe DEX online'); break;
  default: exit;
}

smarty_displayCommonPageWithSkin("$p.ihtml");
?>
