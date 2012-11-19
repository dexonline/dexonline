<?php
require_once("../../phplib/util.php"); 
util_assertModerator(PRIV_EDIT);
util_assertNotMirror();

$submitButton = util_getRequestParameter('submitButton');
if ($submitButton) {
  $defId = util_getRequestParameter('definitionId');
  $def = Definition::get_by_id($defId);

  // Collect the user choices
  $choices = array();
  foreach ($_REQUEST as $name => $value) {
    if (StringUtil::startsWith($name, 'radio_')) {
      $choices[substr($name, 6)] = $value;
    }
  }

  // Collect the positions of ambiguous abbreviations
  $matches = array();
  AdminStringUtil::markAbbreviations($def->internalRep, $def->sourceId, $matches);
  usort($matches, 'positionCmp');

  $s = $def->internalRep;
  foreach ($matches as $i => $m) {
    if ($choices[count($choices) - 1 - $i] == 'abbrev') {
      $orig = substr($s, $m['position'], $m['length']);
      $replacement = StringUtil::isUppercase(StringUtil::getCharAt($orig, 0)) ? AdminStringUtil::capitalize($m['abbrev']) : $m['abbrev'];
      $s = substr_replace($s, "#{$replacement}#", $m['position'], $m['length']);
    }
  }
  $def->internalRep = $s;
  $def->htmlRep = AdminStringUtil::htmlize($def->internalRep, $def->sourceId);
  $def->abbrevReview = ABBREV_REVIEW_COMPLETE;
  $def->save();
}

$MARKER = 'DEADBEEF'; // any string that won't occur naturally in a definition

$def = Model::factory('Definition')->raw_query('select * from Definition where status != ' . ST_DELETED .
                                               ' and abbrevReview = ' . ABBREV_AMBIGUOUS . ' order by rand() limit 1', null)->find_one();

if ($def) {
  // Collect the positions of ambiguous abbreviations
  $matches = array();
  AdminStringUtil::markAbbreviations($def->internalRep, $def->sourceId, $matches);
  usort($matches, 'positionCmp');

  // Inject our marker around each ambiguity and htmlize the definition
  $s = $def->internalRep;
  foreach ($matches as $m) {
    $s = substr($s, 0, $m['position']) . " $MARKER " . substr($s, $m['position'], $m['length']) . " $MARKER " . substr($s, $m['position'] + $m['length']);
  }
  $s = AdminStringUtil::htmlize($s, $def->sourceId);

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
  smarty_assign('text', $text);
  smarty_assign('ambiguities', $ambiguities);
}

smarty_assign('def', $def);
smarty_assign('recentLinks', RecentLink::loadForUser());
smarty_assign('sectionTitle', 'Examinare abrevieri');
smarty_addJs('jquery');
smarty_displayAdminPage('admin/randomAbbrevReview.ihtml');

/**
 * Sort matches from last to first
 */
function positionCmp($a, $b) {
  return $b['position'] - $a['position'];
}

?>
