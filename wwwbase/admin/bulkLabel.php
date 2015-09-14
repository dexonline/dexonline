<?php
require_once("../../phplib/util.php"); 
util_assertModerator(PRIV_EDIT);
util_assertNotMirror();

$suffix = util_getRequestParameter('suffix');
$submitButton = util_getRequestParameter('submitButton');

if ($submitButton) {
  foreach ($_REQUEST as $name => $modelId) {
    if (StringUtil::startsWith($name, 'lexem_')) {
      $parts = preg_split('/_/', $name);
      assert(count($parts) == 2);
      assert($parts[0] == 'lexem');
      $lexem = Lexem::get_by_id($parts[1]);

      if ($modelId) {
        $parts = preg_split('/_/', $modelId);
        assert(count($parts) == 2);
        $lm = $lexem->getFirstLexemModel();
        $lm->modelType = $parts[0];
        $lm->modelNumber = $parts[1];
        $lm->restriction = util_getRequestParameter('restr_' . $lexem->id);
        $lm->save();
        $lm->regenerateParadigm();
      } else {
        $lexem->comment = util_getRequestParameter('comment_' . $lexem->id);
        $lexem->save();
      }
    }
  }
  util_redirect("bulkLabel.php?suffix=$suffix");
}

$reverseSuffix = StringUtil::reverse($suffix);

RecentLink::createOrUpdate("Etichetare asistată: -$suffix");

$numLabeled = Model::factory('Lexem')
  ->table_alias('l')
  ->join('LexemModel', 'lm.lexemId = l.id', 'lm')
  ->where_not_equal('lm.modelType', 'T')
  ->where_like('l.reverse', "{$reverseSuffix}%")
  ->count();

// Collect all the models that appear in at least 5% of the already
// labeled lexems. Always select at least one model, in the unlikely case
// that no model has over 5%.
$models = array();
$hasInvariableModel = false;
$dbResult = db_execute("select canonical, modelNumber, count(*) as c " .
                       "from Lexem " .
                       "join LexemModel on lexemId = Lexem.id " .
                       "join ModelType on modelType = code " .
                       "where modelType != 'T' " .
                       "and reverse like '{$reverseSuffix}%' " .
                       "group by canonical, modelNumber " .
                       "order by c desc",
                       PDO::FETCH_ASSOC);
foreach ($dbResult as $row) {
  $modelType = $row['canonical'];
  $modelNumber = $row['modelNumber'];
  $count = $row['c'];
  if (!count($models) || ($count / $numLabeled >= 0.05)) {
    if ($modelType == 'V' || $modelType == 'VT') {
      $m = Model::factory('FlexModel')->where('modelType', 'V')->where('number', $modelNumber)->find_one();
      $models[] = $m;
      $models[] = FlexModel::create('VT', $modelNumber, '', $m->exponent);
    } else if ($modelType == 'A' || $modelType == 'MF') {
      $m = Model::factory('FlexModel')->where('modelType', 'A')->where('number', $modelNumber)->find_one();
      $models[] = $m;
      $models[] = FlexModel::create('MF', $modelNumber, '', $m->exponent);
    } else {
      $models[] = Model::factory('FlexModel')->where('modelType', $modelType)->where('number', $modelNumber)->find_one();
    }
    $hasInvariableModel = $hasInvariableModel || ($modelType == 'I');
  }
}

// Always add the Invariable model
if (!$hasInvariableModel) {
  $models[] = Model::factory('FlexModel')->where('modelType', 'I')->where('number', '1')->find_one();
}

$modelTypes = array();
foreach ($models as $m) {
  $modelTypes[] = ModelType::get_by_code($m->modelType);
}

$lexems = Model::factory('Lexem')
  ->table_alias('l')
  ->select('l.*')
  ->join('LexemModel', 'lm.lexemId = l.id', 'lm')
  ->where('lm.modelType', 'T')
  ->where_like('l.reverse', "{$reverseSuffix}%")
  ->order_by_asc('l.formNoAccent')
  ->limit(20)
  ->find_many();

// $lmMatrix[$i][$j] = lexem model (with inflected forms) for lexem $i and model $j
$lmMatrix = array();
foreach ($lexems as $l) {
  $lm = $l->getFirstLexemModel();
  $lmArray = array();
  foreach ($models as $m) {
    // Force a reload
    $lm = LexemModel::get_by_id($lm->id);
    $lm->modelType = $m->modelType;
    $lm->modelNumber = $m->number;
    $lm->generateInflectedFormMap();
    $lmArray[] = $lm;
  }
  $lmMatrix[] = $lmArray;
}

// Load the definitions for each lexem
$searchResults = array();
foreach ($lexems as $l) {
  $definitions = Definition::loadByLexemId($l->id);
  $searchResults[] = SearchResult::mapDefinitionArray($definitions);
}

SmartyWrap::assign('suffix', $suffix);
SmartyWrap::assign('lexems', $lexems);
SmartyWrap::assign('models', $models);
SmartyWrap::assign('modelTypes', $modelTypes);
SmartyWrap::assign('searchResults', $searchResults);
SmartyWrap::assign('lmMatrix', $lmMatrix);
SmartyWrap::assign('allStatuses', util_getAllStatuses());
SmartyWrap::assign('sectionTitle', "Sufix: -$suffix");
SmartyWrap::assign('recentLinks', RecentLink::loadForUser());
SmartyWrap::addCss('paradigm');
SmartyWrap::displayAdminPage('admin/bulkLabel.tpl');

?>
