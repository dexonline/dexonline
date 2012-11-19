<?php
require_once("../phplib/util.php");

$content = util_getRequestParameter('c');
switch ($content) {
  case 'abrev': $title = 'Abrevieri'; break;
  case 'adminHelp': $title = 'Mini-manual de moderare'; break;
  case 'loc': $title = 'Legendă LOC'; break;
  default: exit;
}

SmartyWrap::assign('title', "$title | DEX online");
SmartyWrap::assign('content', $content);
SmartyWrap::registerOutputFilters();
SmartyWrap::addCss('zepu');
print SmartyWrap::fetch('common/static.ihtml');
?>
