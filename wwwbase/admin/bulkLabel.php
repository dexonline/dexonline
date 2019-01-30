<?php
require_once '../../phplib/Core.php';
User::mustHave(User::PRIV_EDIT);
Util::assertNotMirror();

$suffix = Request::get('suffix');
$saveButton = Request::has('saveButton');

if ($saveButton) {
  foreach ($_REQUEST as $name => $modelId) {
    if (Str::startsWith($name, 'lexeme_')) {
      $parts = preg_split('/_/', $name);
      assert(count($parts) == 2);
      assert($parts[0] == 'lexeme');
      $lexeme = Lexeme::get_by_id($parts[1]);

      if ($modelId) {
        $parts = preg_split('/_/', $modelId);
        assert(count($parts) == 2);
        $lexeme->modelType = $parts[0];
        $lexeme->modelNumber = $parts[1];
        $lexeme->restriction = Request::get('restr_' . $lexeme->id);
        $lexeme->save();
        $lexeme->regenerateParadigm();
      }
    }
  }
  Util::redirect("bulkLabel.php?suffix=$suffix");
}

$reverseSuffix = Str::reverse($suffix);

$numLabeled = Model::factory('Lexeme')
  ->where_not_equal('modelType', 'T')
  ->where_like('reverse', "{$reverseSuffix}%")
  ->count();

// Collect all the models that appear in at least 5% of the already
// labeled lexemes. Always select at least one model, in the unlikely case
// that no model has over 5%.
$models = [];
$hasInvariableModel = false;
$dbResult = DB::execute("select canonical, modelNumber, count(*) as c " .
                       "from Lexeme " .
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

$modelTypes = [];
foreach ($models as $m) {
  $modelTypes[] = ModelType::get_by_code($m->modelType);
}

$lexemes = Model::factory('Lexeme')
  ->where('modelType', 'T')
  ->where_like('reverse', "{$reverseSuffix}%")
  ->order_by_asc('formNoAccent')
  ->limit(20)
  ->find_many();

// $lMatrix[$i][$j] = lexeme (with inflected forms) for lexeme $i and model $j
$lMatrix = [];
foreach ($lexemes as $l) {
  $lArray = [];
  foreach ($models as $m) {
    // Force a reload
    $copy = Lexeme::create($l->form, $m->modelType, $m->number);
    $copy->generateInflectedFormMap();
    $lArray[] = $copy;
  }
  $lMatrix[] = $lArray;
}

// Load the definitions for each lexeme
$searchResults = [];
foreach ($lexemes as $l) {
  $definitions = Definition::loadByEntryIds($l->getEntryIds());
  $searchResults[] = SearchResult::mapDefinitionArray($definitions);
}

SmartyWrap::assign('suffix', $suffix);
SmartyWrap::assign('lexemes', $lexemes);
SmartyWrap::assign('models', $models);
SmartyWrap::assign('modelTypes', $modelTypes);
SmartyWrap::assign('searchResults', $searchResults);
SmartyWrap::assign('lMatrix', $lMatrix);
SmartyWrap::addCss('paradigm', 'admin');
SmartyWrap::display('admin/bulkLabel.tpl');
