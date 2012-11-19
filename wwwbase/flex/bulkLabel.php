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

$reverseSuffix = StringUtil::reverse($suffix);

RecentLink::createOrUpdate("Etichetare asistată: -$suffix");

$numLabeled = Model::factory('Lexem')->where_not_equal('modelType', 'T')->where_like('reverse', "{$reverseSuffix}%")->count();

// Collect all the models that appear in at least 5% of the already
// labeled lexems. Always select at least one model, in the unlikely case
// that no model has over 5%.
$models = array();
$hasInvariableModel = false;
$dbResult = db_execute("select modelType, modelNumber, count(*) as c from Lexem where modelType != 'T' and reverse like '{$reverseSuffix}%' " .
                       "group by modelType, modelNumber order by c desc", PDO::FETCH_ASSOC);
foreach ($dbResult as $row) {
  $modelType = $row['modelType'];
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

$lexems = Model::factory('Lexem')->where('modelType', 'T')->where_like('reverse', "{$reverseSuffix}%")->order_by_asc('formNoAccent')
  ->limit(20)->find_many();

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

SmartyWrap::assign('suffix', $suffix);
SmartyWrap::assign('lexems', $lexems);
SmartyWrap::assign('models', $models);
SmartyWrap::assign('searchResults', $searchResults);
SmartyWrap::assign('ifMapMatrix', $ifMapMatrix);
SmartyWrap::assign('allStatuses', util_getAllStatuses());
SmartyWrap::assign('sectionTitle', "Sufix: -$suffix");
SmartyWrap::assign('recentLinks', RecentLink::loadForUser());
SmartyWrap::addCss('paradigm');
SmartyWrap::displayAdminPage('flex/bulkLabel.ihtml');

?>
