<?php
require_once("../../phplib/Core.php"); 
User::mustHave(User::PRIV_EDIT);
Util::assertNotMirror();

$saveButton = Request::has('saveButton');

if ($saveButton) {
  $defId = Request::get('definitionId');
  $def = Definition::get_by_id($defId);

  // Collect the user choices
  $choices = array();
  foreach ($_REQUEST as $name => $value) {
    if (Str::startsWith($name, 'radio_')) {
      $choices[substr($name, 6)] = $value;
    }
  }

  // Collect the positions of ambiguous abbreviations
  $matches = array();
  Abbrev::markAbbreviations($def->internalRep, $def->sourceId, $matches);
  usort($matches, 'positionCmp');

  $s = $def->internalRep;
  foreach ($matches as $i => $m) {
    if ($choices[count($choices) - 1 - $i] == 'abbrev') {
      $orig = substr($s, $m['position'], $m['length']);
      $replacement = Str::isUppercase(Str::getCharAt($orig, 0)) ? Str::capitalize($m['abbrev']) : $m['abbrev'];
      $s = substr_replace($s, "#{$replacement}#", $m['position'], $m['length']);
    }
  }
  $def->internalRep = $s;
  $def->htmlRep = Str::htmlize($def->internalRep, $def->sourceId);
  $def->abbrevReview = Definition::ABBREV_REVIEW_COMPLETE;
  $def->save();
}

$MARKER = 'DEADBEEF'; // any string that won't occur naturally in a definition
$def = null;
$ids = DB::getArray(sprintf('select id from Definition where status != %d and abbrevReview = %d',
                            Definition::ST_DELETED,
                            Definition::ABBREV_AMBIGUOUS));
if (count($ids)) {
  $defId = $ids[array_rand($ids, 1)];
  $def = Definition::get_by_id($defId);

  // Collect the positions of ambiguous abbreviations
  $matches = array();
  Abbrev::markAbbreviations($def->internalRep, $def->sourceId, $matches);
  usort($matches, 'positionCmp');

  // Inject our marker around each ambiguity and htmlize the definition
  $s = $def->internalRep;
  foreach ($matches as $m) {
    $s = substr($s, 0, $m['position']) . " $MARKER " . substr($s, $m['position'], $m['length']) . " $MARKER " . substr($s, $m['position'] + $m['length']);
  }
  $s = Str::htmlize($s, $def->sourceId);

  // Split the definition into n ambiguities and n+1 bits of text between the ambiguities
  $text = array();
  $ambiguities = array();
  while (($p = strpos($s, $MARKER)) !== false) {
    $chunk = trim(substr($s, 0, $p));
    $s = trim(substr($s, $p + strlen($MARKER)));
    if (count($text) == count($ambiguities)) {
      $text[] = $chunk;
    } else {
      $ambiguities[] = $chunk;
    }
  }
  $text[] = trim($s);
  SmartyWrap::assign('text', $text);
  SmartyWrap::assign('ambiguities', $ambiguities);
}

SmartyWrap::assign('def', $def);
SmartyWrap::addCss('admin');
SmartyWrap::display('admin/randomAbbrevReview.tpl');

/**
 * Sort matches from last to first
 */
function positionCmp($a, $b) {
  return $b['position'] - $a['position'];
}
