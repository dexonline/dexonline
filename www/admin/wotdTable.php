<?php
require_once '../../lib/Core.php';
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

Smart::assign([
  'imageList' => $imageList,
  'assistantDates' => $assistantDates,
]);
Smart::addCss('jqgrid', 'jqueryui', 'admin', 'bootstrap-datepicker');
Smart::addJs('jqgrid', 'jqueryui', 'select2Dev', 'bootstrap-datepicker');
Smart::display('admin/wotdTable.tpl');
