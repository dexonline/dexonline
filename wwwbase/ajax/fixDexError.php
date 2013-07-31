<?php

define('FILE_NAME', __DIR__ . '/../../corectii DEXOnline.txt');

require_once __DIR__ . '/../../phplib/util.php';
util_assertModerator(PRIV_EDIT);
util_assertNotMirror();

$id = util_getRequestParameter('id');
$def = Definition::get_by_id($id);
assert($def);

$lines = file(FILE_NAME);
$lines = array_slice($lines, 1); // First line contains the column descriptions

foreach ($lines as $line) {
  $parts = explode("\t", trim($line));
  assert(count($parts) == 4);
  list($id, $wrong, $right, $code) = $parts;
  if ($id == $def->id) {
    $def->internalRep = $right;
    $def->htmlRep = AdminStringUtil::htmlize($right, $def->sourceId);
    $def->lexicon = AdminStringUtil::extractLexicon($def);
    $def->save();
    break; // I know. Shut up.
  }
}

?>
