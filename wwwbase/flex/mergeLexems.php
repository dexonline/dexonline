<?
require_once("../../phplib/util.php"); 
util_assertModeratorStatus();
util_assertNotMirror();
util_hideEmptyRequestParameters();

$modelType = util_getRequestParameter('modelType');
$submitButton = util_getRequestParameter('submitButton');

if ($submitButton) {
  $lexemsToDelete = array();
  foreach ($_REQUEST as $name => $value) {
    if (text_startsWith($name, 'merge_') && $value) {
      $parts = split('_', $name);
      assert(count($parts) == 3);
      assert($parts[0] == 'merge');
      $src = Lexem::load($parts[1]);
      $dest = Lexem::load($parts[2]);

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
$dbResult = db_selectPluralLexemsByModelType($modelType);

$lexems = array();
while ($row = mysql_fetch_assoc($dbResult)) {
  $lexem = Lexem::createFromDbRow($row);
  $lexem->matches = array();
  $wordLists = WordList::loadByUnaccented($lexem->unaccented);

  foreach ($wordLists as $wl) {
    if (in_array($wl->inflectionId, $PLURAL_INFLECTIONS) && $wl->lexemId != $lexem->id) {
      $lexem->matches[] = Lexem::load($wl->lexemId);
    }
  }

  if (count($lexem->matches)) {
    $lexem->wordLists = WordList::loadByLexemId($lexem->id);
    // When a plural LOC lexem is merged into a non-LOC singular, we end up losing some word forms from LOC.
    // Therefore, we have to add the singular lexem to LOC as well. Matei says it is ok to expand LOC this way.
    $srcWls = loadWlArrayByLexemId($lexem->id);
    foreach ($lexem->matches as $match) {
      $destWls = loadWlArrayByLexemId($match->id);
      $match->addedForms = array();
      $match->lostForms = array();
      if ($lexem->isLoc && !$match->isLoc) {
        // Forms that are going to be added to LOC
        foreach ($destWls as $destWl) {
          if (!in_array($destWl, $srcWls)) {
            $match->addedForms[] = $destWl;
          }
        }
      }
      // Forms that will disappear after the merge -- these should be rare.
      foreach ($srcWls as $srcWl) {
        if (!in_array($srcWl, $destWls)) {
          $match->lostForms[] = $srcWl;
        }
      }
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

/** Returns an array containing only the accented forms, not the entire WordList objects **/
function loadWlArrayByLexemId($lexemId) {
  $wls = WordList::loadByLexemId($lexemId);
  $result = array();
  foreach ($wls as $wl) {
    $result[] = $wl->form;
  }
  return $result;
}

?>
