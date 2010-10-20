<?
require_once("../../phplib/util.php"); 
util_assertModerator(PRIV_EDIT);
util_assertNotMirror();

$suffix = util_getRequestParameter('suffix');
$submitButton = util_getRequestParameter('submitButton');

if ($submitButton) {
  foreach ($_REQUEST as $name => $modelId) {
    if (text_startsWith($name, 'lexem_')) {
      $parts = preg_split('/_/', $name);
      assert(count($parts) == 2);
      assert($parts[0] == 'lexem');
      $lexem = Lexem::get("id = " . $parts[1]);

      if ($modelId) {
        $parts = preg_split('/_/', $modelId);
        assert(count($parts) == 2);
        $lexem->modelType = $parts[0];
        $lexem->modelNumber = $parts[1];
        $restrArray = util_getRequestParameter('restr_' . $lexem->id);
        $restriction = $restrArray ? implode($restrArray, '') : '';
        $lexem->restriction = $restriction;
        $lexem->save();
        $lexem->regenerateParadigm();
      } else {
        $lexem->comment = util_getRequestParameter('comment_' . $lexem->id);
        $lexem->save();
      }
    }
  }
  util_redirect("bulkLabel.php?suffix=$suffix");
}

$reverseSuffix = text_reverse($suffix);

RecentLink::createOrUpdate("Etichetare asistată: -$suffix");

$numLabeled = db_getSingleValue("select count(*) from Lexem where modelType != 'T' and reverse like '{$reverseSuffix}%'");

// Collect all the models that appear in at least 5% of the already
// labeled lexems. Always select at least one model, in the unlikely case
// that no model has over 5%.
$models = array();
$hasInvariableModel = false;
$dbResult = db_execute("select modelType, modelNumber, count(*) as c from Lexem where modelType != 'T' and reverse like '{$reverseSuffix}%' " .
                       "group by modelType, modelNumber order by c desc");
while (!$dbResult->EOF) {
  $modelType = $dbResult->fields['modelType'];
  $modelNumber = $dbResult->fields['modelNumber'];
  $count = $dbResult->fields['c'];
  $dbResult->MoveNext();
  if (!count($models) || ($count / $numLabeled >= 0.05)) {
    if ($modelType == 'V' || $modelType == 'VT') {
      $m = Model::get("modelType = 'V' and number = '{$modelNumber}'");
      $models[] = $m;
      $models[] = new Model('VT', $modelNumber, '', $m->exponent);
    } else if ($modelType == 'A' || $modelType == 'MF') {
      $m = Model::get("modelType = 'A' and number = '{$modelNumber}'");
      $models[] = $m;
      $models[] = new Model('MF', $modelNumber, '', $m->exponent);
    } else {
      $models[] = Model::get("modelType = '{$modelType}' and number = '{$modelNumber}'");
    }
    $hasInvariableModel = $hasInvariableModel || ($modelType == 'I');
  }
}

// Always add the Invariable model
if (!$hasInvariableModel) {
  $models[] = Model::get("modelType = 'I' and number = '1'");
}

$lexems = db_find(new Lexem(), "modelType = 'T' and reverse like '{$reverseSuffix}%' order by formNoAccent limit 20");

// $ifMapMatrix[$i][$j] = array of InflectedForms for lexem $i and model $j
$ifMapMatrix = array();
foreach ($lexems as $l) {
  $origModelType = $l->modelType;
  $origModelNumber = $l->modelNumber;
  $ifMapArray = array();
  foreach ($models as $m) {
    $l->modelType = $m->modelType;
    $l->modelNumber = $m->number;
    $if = $l->generateParadigm();
    if (is_array($if)) {
      $ifMapArray[] = InflectedForm::mapByInflectionRank($if);
    } else {
      $ifMapArray[] = null;
    }
  }
  $l->modelType = $origModelType;
  $l->modelNumber = $origModelNumber;
  $ifMapMatrix[] = $ifMapArray;
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
smarty_assign('ifMapMatrix', $ifMapMatrix);
smarty_assign('allStatuses', util_getAllStatuses());
smarty_assign('sectionTitle', "Sufix: $suffix");
smarty_assign('recentLinks', RecentLink::loadForUser());
smarty_displayWithoutSkin('flex/bulkLabel.ihtml');

?>
