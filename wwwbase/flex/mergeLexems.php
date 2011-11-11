<?php
require_once("../../phplib/util.php"); 
util_assertModerator(PRIV_EDIT);
util_assertNotMirror();
util_hideEmptyRequestParameters();
DebugInfo::disable();

$modelType = util_getRequestParameter('modelType');
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
      if ($src->isLoc && !$dest->isLoc) {
        $dest->isLoc = true;
        $dest->save();
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
$dbResult = db_execute("select * from Lexem where {$whereClause} order by formNoAccent", PDO::FETCH_ASSOC);

$lexems = array();
foreach ($dbResult as $row) {
  $lexem = Model::factory('Lexem')->create($row);
  $matches = array();
  $ifs = InflectedForm::get_all_by_formNoAccent($lexem->formNoAccent);

  foreach ($ifs as $if) {
    if (in_array($if->inflectionId, $PLURAL_INFLECTIONS) && $if->lexemId != $lexem->id) {
      $matches[] = Lexem::get_by_id($if->lexemId);
    }
  }
  $lexem->matches = $matches;

  if (count($lexem->matches)) {
    $lexem->ifs = InflectedForm::loadByLexemId($lexem->id);
    // When a plural LOC lexem is merged into a non-LOC singular, we end up losing some word forms from LOC.
    // Therefore, we have to add the singular lexem to LOC as well. Matei says it is ok to expand LOC this way.
    $srcIfs = loadIfArrayByLexemId($lexem->id);
    foreach ($lexem->matches as $match) {
      $destIfs = loadIfArrayByLexemId($match->id);
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
smarty_assign('sectionTitle', 'Unificare lexeme');
smarty_assign('recentLinks', RecentLink::loadForUser());
smarty_assign('modelType', $modelType);
smarty_assign('lexems', $lexems);
smarty_displayWithoutSkin('flex/mergeLexems.ihtml');


/***************************************************/

/** Returns an array containing only the accented forms, not the entire InflectedForm objects **/
function loadIfArrayByLexemId($lexemId) {
  $ifs = InflectedForm::loadByLexemId($lexemId);
  $result = array();
  foreach ($ifs as $if) {
    $result[] = $if->form;
  }
  return $result;
}

?>
