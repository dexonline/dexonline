<?php
require_once("../../phplib/util.php"); 
util_assertModerator(PRIV_EDIT);
util_assertNotMirror();
util_hideEmptyRequestParameters();
DebugInfo::disable();

$modelType = util_getRequestParameterWithDefault('modelType', 'M');
$submitButton = util_getRequestParameter('submitButton');

if ($submitButton) {
  $lexemsToDelete = array();
  foreach ($_REQUEST as $name => $value) {
    if (StringUtil::startsWith($name, 'merge_') && $value) {
      $parts = preg_split('/_/', $name);
      assert(count($parts) == 3);
      assert($parts[0] == 'merge');
      $src = Lexem::get_by_id($parts[1]);
      $dest = Lexem::get_by_id($parts[2]);

      // Merge $src into $dest
      $defs = Definition::loadByLexemId($src->id);
      foreach ($defs as $def) {
        LexemDefinitionMap::associate($dest->id, $def->id);
      }

      // Add $dest to LOC if $src is in LOC
      if ($src->isLoc() && !$dest->isLoc()) {
        $lm = $dest->getFirstLexemModel();
        $lm->isLoc = true;
        $lm->save();
      }

      // Delay the deletion because we might have to merge $src with other lexems.
      $lexemsToDelete[] = $src;
    }
  }
  foreach ($lexemsToDelete as $lexem) {
    $lexem->delete();
  }
  util_redirect("mergeLexems.php?modelType={$modelType}");
  exit;
}

$PLURAL_INFLECTIONS = array(3, 11, 19, 27, 35);
if ($modelType == 'T') {
  $whereClause = 'modelType = "T"';
} else if ($modelType) {
  $whereClause = "modelType = '{$modelType}' and restriction like '%P%'";
} else {
  $whereClause = '(modelType = "T") or (modelType in ("M", "F", "N") and restriction like "%P%")';
}
$dbResult = db_execute("select distinct l.* " .
                       "from Lexem l " .
                       "join LexemModel lm on lm.lexemId = l.id " .
                       "where {$whereClause} " .
                       "order by formNoAccent",
                       PDO::FETCH_ASSOC);

$lexems = array();
foreach ($dbResult as $row) {
  $lexem = Model::factory('Lexem')->create($row);
  $matches = array();

  $lexem->matches = Model::factory('Lexem')
    ->table_alias('l')
    ->select('l.*')
    ->distinct()
    ->join('LexemModel', 'lm.lexemId = l.id', 'lm')
    ->join('InflectedForm', 'i.lexemModelId = lm.id', 'i')
    ->where('i.formNoAccent', $lexem->formNoAccent)
    ->where_in('i.inflectionId', $PLURAL_INFLECTIONS)
    ->where_not_equal('l.id', $lexem->id)
    ->find_many();

  if (count($lexem->matches)) {
    // $lexem->loadInflectedForms();
    // When a plural LOC lexem is merged into a non-LOC singular, we end up losing some word forms from LOC.
    // Therefore, we have to add the singular lexem to LOC as well. Matei says it is ok to expand LOC this way.
    $srcIfs = loadIfArray($lexem);
    foreach ($lexem->matches as $match) {
      $destIfs = loadIfArray($match);
      $addedForms = array();
      $lostForms = array();
      if ($lexem->isLoc && !$match->isLoc) {
        // Forms that are going to be added to LOC
        foreach ($destIfs as $destIf) {
          if (!in_array($destIf, $srcIfs)) {
            $addedForms[] = $destIf;
          }
        }
      }
      // Forms that will disappear after the merge -- these should be rare.
      foreach ($srcIfs as $srcIf) {
        if (!in_array($srcIf, $destIfs)) {
          $lostForms[] = $srcIf;
        }
      }
      $lexem->addedForms = $addedForms;
      $lexem->lostForms = $lostForms;
    }
    $lexems[] = $lexem;
  }
}

RecentLink::createOrUpdate('Unificare lexeme');
SmartyWrap::assign('sectionTitle', 'Unificare lexeme');
SmartyWrap::assign('recentLinks', RecentLink::loadForUser());
SmartyWrap::assign('modelType', $modelType);
SmartyWrap::assign('lexems', $lexems);
SmartyWrap::displayAdminPage('flex/mergeLexems.ihtml');


/***************************************************/

/** Returns an array containing only the accented forms, not the entire InflectedForm objects **/
function loadIfArray($lexem) {
  $lm = $lexem->getFirstLexemModel();
  $ifs = $lm->loadInflectedForms();
  $result = array();
  foreach ($ifs as $if) {
    $result[] = $if->form;
  }
  return $result;
}

?>
