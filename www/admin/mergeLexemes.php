<?php
require_once '../../lib/Core.php';
User::mustHave(User::PRIV_EDIT);
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

      // Delay the deletion because we might have to merge $src with other lexemes.
      $lexemesToDelete[] = $src;
    }
  }
  foreach ($lexemesToDelete as $lexeme) {
    $lexeme->delete();
  }
  Util::redirect("mergeLexemes.php?modelType={$modelType}");
}

const PLURAL_INFLECTIONS = [3, 11, 19, 27, 35];
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
    ->where_in('i.inflectionId', PLURAL_INFLECTIONS)
    ->where_not_equal('l.id', $lexeme->id)
    ->find_many();

  if (count($lexeme->matches)) {
    $srcIfs = loadIfArray($lexeme);
    foreach ($lexeme->matches as $match) {
      $destIfs = loadIfArray($match);
      $lostForms = [];
      // Forms that will disappear after the merge -- these should be rare.
      foreach ($srcIfs as $srcIf) {
        if (!in_array($srcIf, $destIfs)) {
          $lostForms[] = $srcIf;
        }
      }
      $lexeme->lostForms = $lostForms;
    }
    $lexemes[] = $lexeme;
  }
}

Smart::assign('modelType', $modelType);
Smart::assign('lexemes', $lexemes);
Smart::addResources('admin');
Smart::display('admin/mergeLexemes.tpl');


/***************************************************/

/** Returns an array containing only the accented forms, not the entire InflectedForm objects **/
function loadIfArray($lexeme) {
  $ifs = $lexeme->loadInflectedForms();
  return Util::objectProperty($ifs, 'form');
}
