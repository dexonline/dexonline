<?php
require_once("../../phplib/util.php"); 
User::require(User::PRIV_EDIT);
util_assertNotMirror();
DebugInfo::disable();

$modelType = Request::get('modelType', 'M');
$saveButton = Request::has('saveButton');

if ($saveButton) {
  $lexemsToDelete = [];
  foreach ($_REQUEST as $name => $value) {
    if (StringUtil::startsWith($name, 'merge_') && $value) {
      $parts = preg_split('/_/', $name);
      assert(count($parts) == 3);
      assert($parts[0] == 'merge');
      $src = Lexem::get_by_id($parts[1]);
      $dest = Lexem::get_by_id($parts[2]);

      $srcEls = EntryLexem::get_all_by_lexemId($src->id);
      $destEls = EntryLexem::get_all_by_lexemId($dest->id);

      // Merge $src into $dest
      foreach ($srcEls as $srcEl) {
        $eds = EntryDefinition::get_all_by_entryId($srcEl->entryId);
        foreach ($eds as $ed) {
          foreach ($destEls as $destEl) {
            EntryDefinition::associate($destEl->entryId, $ed->definitionId);
          }
          EntryDefinition::dissociate($srcEl->entryId, $ed->definitionId);
        }
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
}

$PLURAL_INFLECTIONS = array(3, 11, 19, 27, 35);
if ($modelType == 'T') {
  $whereClause = 'modelType = "T"';
} else if ($modelType) {
  $whereClause = "modelType = '{$modelType}' and restriction like '%P%'";
} else {
  $whereClause = '(modelType = "T") or (modelType in ("M", "F", "N") and restriction like "%P%")';
}
// TODO speed up the page for T lexems
$dbResult = DB::execute("select distinct l.* " .
                       "from Lexem l " .
                       "where {$whereClause} " .
                       "order by formNoAccent",
                       PDO::FETCH_ASSOC);

$lexems = [];
foreach ($dbResult as $row) {
  $lexem = Model::factory('Lexem')->create($row);

  $lexem->matches = Model::factory('Lexem')
    ->table_alias('l')
    ->select('l.*')
    ->distinct()
    ->join('InflectedForm', 'i.lexemId = l.id', 'i')
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

SmartyWrap::assign('modelType', $modelType);
SmartyWrap::assign('lexems', $lexems);
SmartyWrap::addCss('admin');
SmartyWrap::display('admin/mergeLexems.tpl');


/***************************************************/

/** Returns an array containing only the accented forms, not the entire InflectedForm objects **/
function loadIfArray($lexem) {
  $ifs = $lexem->loadInflectedForms();
  return Util::objectProperty($ifs, 'form');
}

?>
