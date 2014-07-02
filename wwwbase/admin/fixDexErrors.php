<?php

/**
 * Fixes errors kindly reported by Ștefan Cioc.
 * After saving his text file, first change its encoding to UTF-8. You can do this with emacs: C-x C-m f utf-8, then save.
 */

define('FILE_NAME', __DIR__ . '/../../corectii DEXOnline.txt');

require_once __DIR__ . '/../../phplib/util.php';
util_assertModerator(PRIV_EDIT);
util_assertNotMirror();

$lines = file(FILE_NAME);
$lines = array_slice($lines, 1); // First line contains the column descriptions

$data = array();
foreach ($lines as $i => $line) {
  $parts = explode("\t", trim($line));
  if (count($parts) != 4) {
    printf("Linia %d conține %d părți: [%s]\n", $i, count($parts), $line);
    exit;
  }

  list($id, $wrong, $right, $code) = $parts;
  $def = Definition::get_by_id($id);
  if (!$def) {
    printf("[ID:$id] definiția nu există\n");
  }

  if ($def->internalRep != $right) {
    $data[] = array('def' => $def,
                    'diff' => SimpleDiff::htmlDiff($def->internalRep, $right));
  }  
}

SmartyWrap::assign('data', $data);
SmartyWrap::assign('recentLinks', RecentLink::loadForUser());
SmartyWrap::assign('sectionTitle', 'Corectare erori raportate');
SmartyWrap::assign('sectionCount', count($data));
SmartyWrap::addJs('jquery');
SmartyWrap::displayAdminPage('admin/fixDexErrors.ihtml');

?>
