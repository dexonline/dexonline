<?php
require_once("../../phplib/Core.php"); 
User::mustHave(User::PRIV_EDIT);
Util::assertNotMirror();
DebugInfo::disable();

$modelType = Request::get('modelType', 'M');
$saveButton = Request::has('saveButton');

if ($saveButton) {
  $lexemesToDelete = [];
  foreach ($_REQUEST as $name => $value) {
    if (Str::startsWith($name, 'merge_') && $value) {
      $parts = preg_split('/_/', $name);
      assert(count($parts) == 3);
      assert($parts[0] == 'merge');
      $src = Lexeme::get_by_id($parts[1]);
      $dest = Lexeme::get_by_id($parts[2]);

      $srcEls = EntryLexeme::get_all_by_lexemeId($src->id);
      $destEls = EntryLexeme::get_all_by_lexemeId($dest->id);

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

      // Delay the deletion because we might have to merge $src with other lexemes.
      $lexemesToDelete[] = $src;
    }
  }
  foreach ($lexemesToDelete as $lexeme) {
    $lexeme->delete();
  }
  Util::redirect("mergeLexemes.php?modelType={$modelType}");
}

$PLURAL_INFLECTIONS = [3, 11, 19, 27, 35];
if ($modelType == 'T') {
  $whereClause = 'modelType = "T"';
} else if ($modelType) {
  $whereClause = "modelType = '{$modelType}' and restriction like '%P%'";
} else {
  $whereClause = '(modelType = "T") or (modelType in ("M", "F", "N") and restriction like "%P%")';
}
// TODO speed up the page for T lexemes
$dbResult = DB::execute("select distinct l.* " .
                       "from Lexeme l " .
                       "where {$whereClause} " .
                       "order by formNoAccent",
                       PDO::FETCH_ASSOC);

$lexemes = [];
foreach ($dbResult as $row) {
  $lexeme = Model::factory('Lexeme')->create($row);

  $lexeme->matches = Model::factory('Lexeme')
    ->table_alias('l')
    ->select('l.*')
    ->distinct()
    ->join('InflectedForm', 'i.lexemeId = l.id', 'i')
    ->where('i.formNoAccent', $lexeme->formNoAccent)
    ->where_in('i.inflectionId', $PLURAL_INFLECTIONS)
    ->where_not_equal('l.id', $lexeme->id)
    ->find_many();

  if (count($lexeme->matches)) {
    // $lexeme->loadInflectedForms();
    // When a plural LOC lexeme is merged into a non-LOC singular, we end up losing some word forms from LOC.
    // Therefore, we have to add the singular lexeme to LOC as well. Matei says it is ok to expand LOC this way.
    $srcIfs = loadIfArray($lexeme);
    foreach ($lexeme->matches as $match) {
      $destIfs = loadIfArray($match);
      $addedForms = [];
      $lostForms = [];
      if ($lexeme->isLoc && !$match->isLoc) {
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
      $lexeme->addedForms = $addedForms;
      $lexeme->lostForms = $lostForms;
    }
    $lexemes[] = $lexeme;
  }
}

SmartyWrap::assign('modelType', $modelType);
SmartyWrap::assign('lexemes', $lexemes);
SmartyWrap::addCss('admin');
SmartyWrap::display('admin/mergeLexemes.tpl');


/***************************************************/

/** Returns an array containing only the accented forms, not the entire InflectedForm objects **/
function loadIfArray($lexeme) {
  $ifs = $lexeme->loadInflectedForms();
  return Util::objectProperty($ifs, 'form');
}
