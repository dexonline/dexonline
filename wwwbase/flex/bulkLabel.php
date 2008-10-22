<?
require_once("../../phplib/util.php"); 
util_assertModeratorStatus();
util_assertNotMirror();

$suffix = util_getRequestParameter('suffix');
$submitButton = util_getRequestParameter('submitButton');

if ($submitButton) {
  foreach ($_REQUEST as $name => $modelId) {
    if (text_startsWith($name, 'lexem_')) {
      $parts = split('_', $name);
      assert(count($parts) == 2);
      assert($parts[0] == 'lexem');
      $lexem = Lexem::load($parts[1]);

      if ($modelId) {
        $parts = split('_', $modelId);
        assert(count($parts) == 2);
        $lexem->modelType = $parts[0];
        $lexem->modelNumber = $parts[1];
        $restrArray = util_getRequestParameter('restr_' . $lexem->id);
        $restriction = $restrArray ? implode($restrArray, '') : '';
        $lexem->restriction = $restriction;
        $lexem->save();
        $lexem->regenerateParadigm();
      } else {
        $comment = util_getRequestParameter('comment_' . $lexem->id);
        if (!$comment) {
          $comment = 'De revizuit (adăugat automat)';
        }
        $lexem->comment = $comment;
        $lexem->save();
      }
    }
  }
  util_redirect("bulkLabel.php?suffix=$suffix");
}

$reverseSuffix = text_reverse($suffix);

RecentLink::createOrUpdate("Etichetare asistată: -$suffix");

$numLabeled = db_countLabeledBySuffix($reverseSuffix);

// Collect all the models that appear in at least 5% of the already
// labeled lexems. Always select at least one model, in the unlikely case
// that no model has over 5%.
$models = array();
$hasInvariableModel = false;
$dbResult = db_selectModelsBySuffix($reverseSuffix);
while ($dbRow = mysql_fetch_assoc($dbResult)) {
  $modelType = $dbRow['lexem_model_type'];
  $modelNumber = $dbRow['lexem_model_no'];
  $count = $dbRow['c'];
  if (!count($models) || ($count / $numLabeled >= 0.05)) {
    $m = Model::loadByTypeNumber($modelType, $modelNumber);
    if (!$m) {
      $canonical = Model::loadCanonicalByTypeNumber($modelType, $modelNumber);
      $m = Model::create($modelType, $modelNumber, '', $canonical->exponent);
    }
    $models[] = $m;
    $hasInvariableModel = $hasInvariableModel || ($modelType == 'I');
  }
}

// Always add the Invariable model
if (!$hasInvariableModel) {
  $models[] = Model::loadByTypeNumber('I', 1);
}

// Load at most 10 lexems having this suffix and flex them according
// to each possible model.
$lexems = Lexem::loadTemporaryBySuffix($reverseSuffix);

// $wlMapMatrix[$i][$j] = array of WordLists for lexem $i and model $j
$wlMapMatrix = array();
foreach ($lexems as $l) {
  $origModelType = $l->modelType;
  $origModelNumber = $l->modelNumber;
  $wlMapArray = array();
  foreach ($models as $m) {
    $l->modelType = $m->modelType;
    $l->modelNumber = $m->number;
    $wl = $l->generateParadigm();
    if (is_array($wl)) {
      $wlMapArray[] = WordList::mapByInflectionId($wl);
    } else {
      $wlMapArray[] = null;
    }
  }
  $l->modelType = $origModelType;
  $l->modelNumber = $origModelNumber;
  $wlMapMatrix[] = $wlMapArray;
}

// Load the definitions for each lexem
$searchResults = array();
foreach ($lexems as $l) {
  $definitions = Definition::loadByLexemId($l->id);
  $searchResults[] = SearchResult::mapDefinitionArray($definitions);
}

smarty_assign('suffix', $suffix);
smarty_assign('lexems', $lexems);
smarty_assign('models', $models);
smarty_assign('searchResults', $searchResults);
smarty_assign('wlMapMatrix', $wlMapMatrix);
smarty_assign('allStatuses', util_getAllStatuses());
smarty_assign('sectionTitle', "Sufix: $suffix");
smarty_assign('recentLinks', RecentLink::loadForUser());
smarty_displayWithoutSkin('flex/bulkLabel.ihtml');

?>
