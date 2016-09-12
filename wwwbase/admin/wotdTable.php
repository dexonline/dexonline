<?php
require_once("../../phplib/util.php");
util_assertModerator(PRIV_WOTD);
util_assertNotMirror();
RecentLink::add('Cuvântul zilei');

// Load the image list
$imageList = [''];
$staticFiles = file(Config::get('static.url') . 'fileList.txt');

foreach ($staticFiles as $s) {
  $s = trim($s);
  $ext = strtolower(pathinfo($s, PATHINFO_EXTENSION));
  if (StringUtil::startsWith($s, 'img/wotd/') &&
      in_array($ext, ['jpeg', 'jpg', 'png', 'gif']) &&
      (strpos($s, 'thumb') === false)) {
    $imageList[] = substr($s, 9); // Skip the 'img/wotd/' characters
  }
}

SmartyWrap::assign('downloadYear', date("Y",strtotime("+1 month")));
SmartyWrap::assign('downloadMonth', date("m",strtotime("+1 month")));
SmartyWrap::assign('imageList', $imageList);
SmartyWrap::addCss('jqgrid', 'jqueryui', 'select2', 'admin');
SmartyWrap::addJs('jqgrid', 'jqueryui', 'select2');
SmartyWrap::display('admin/wotdTable.tpl');
?>
