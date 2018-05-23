<?php
require_once("../../phplib/Core.php");
User::mustHave(User::PRIV_WOTD);
Util::assertNotMirror();
RecentLink::add('Cuvântul zilei');

// Load the image list
$imageList = [''];
$staticFiles = file(Config::get('static.url') . 'fileList.txt');

foreach ($staticFiles as $s) {
  $s = trim($s);
  $ext = strtolower(pathinfo($s, PATHINFO_EXTENSION));
  if (Str::startsWith($s, 'img/wotd/') &&
      in_array($ext, ['jpeg', 'jpg', 'png', 'gif']) &&
      (strpos($s, 'thumb') === false)) {
    $imageList[] = substr($s, 9); // Skip the 'img/wotd/' characters
  }
}

SmartyWrap::assign('downloadYear', date("Y",strtotime("+1 month")));
SmartyWrap::assign('downloadMonth', date("m",strtotime("+1 month")));
SmartyWrap::assign('imageList', $imageList);
SmartyWrap::addCss('jqgrid', 'jqueryui', 'admin', 'bootstrap-datepicker');
SmartyWrap::addJs('jqgrid', 'jqueryui', 'select2Dev', 'bootstrap-datepicker');
SmartyWrap::display('admin/wotdTable.tpl');
