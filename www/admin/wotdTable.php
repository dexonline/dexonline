<?php
require_once '../../phplib/Core.php';
User::mustHave(User::PRIV_WOTD);
RecentLink::add('Cuvântul zilei');

// Load the image list
$imageList = [''];
$staticFiles = file(Config::STATIC_URL . 'fileList.txt');

foreach ($staticFiles as $s) {
  $s = trim($s);
  $ext = strtolower(pathinfo($s, PATHINFO_EXTENSION));
  if (Str::startsWith($s, 'img/wotd/') &&
      in_array($ext, ['jpeg', 'jpg', 'png', 'gif']) &&
      (strpos($s, 'thumb') === false)) {
    $imageList[] = substr($s, 9); // Skip the 'img/wotd/' characters
  }
}

$assistantDates = [
  strtotime("+1 month"),
  strtotime("+2 month"),
  strtotime("+3 month"),
];

SmartyWrap::assign([
  'imageList' => $imageList,
  'assistantDates' => $assistantDates,
]);
SmartyWrap::addCss('jqgrid', 'jqueryui', 'admin', 'bootstrap-datepicker');
SmartyWrap::addJs('jqgrid', 'jqueryui', 'select2Dev', 'bootstrap-datepicker');
SmartyWrap::display('admin/wotdTable.tpl');
