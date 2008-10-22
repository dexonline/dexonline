<?
require_once("../../phplib/util.php"); 
util_assertFlexModeratorStatus();
util_assertNotMirror();

$updateModels = util_getRequestParameter('updateModels');
$modelType = util_getRequestParameter('modelType');

if ($updateModels) {
  foreach ($_REQUEST as $name => $value) {
    if (text_startsWith($name, 'model_') && $value != '0') {
      $parts = split('_', $name);
      assert(count($parts) == 2);
      assert($parts[0] == 'model');
      $modelNumber = $parts[1];

      // If given, add a comment to the lexems that were shown for this model.
      $comment = util_getRequestParameter("com_$modelNumber");
      if ($comment) {
        $lexemIds = util_getRequestParameter("lexems_$modelNumber");
        foreach ($lexemIds as $lexemId) {
          $lexem = Lexem::load($lexemId);
          $lexem->comment = $comment;
          $lexem->save();
        }
      }

      // Now mark this model with the flag value.
      $m = Model::loadByTypeNumber($modelType, $modelNumber);
      $m->flag = $value;
      $m->save();
    }
  }
  util_redirect("verifyParadigms.php?modelType=$modelType");
}

if ($modelType) {
  RecentLink::createOrUpdate("Verificare paradigme: $modelType");
} else {
  RecentLink::createOrUpdate('Verificare paradigme');
}

if ($modelType) {
  $models = Model::loadByType($modelType);
  $lexems = array();
  $wlMaps = array();

  foreach ($models as $model) {
    // Load the distinct 3-letter suffixes, in descending order of the
    // frequency.
    $dbResult = db_selectModelStatsWithSuffixes($modelType, $model->number);
    $suffixes = array();
    while ($dbRow = mysql_fetch_assoc($dbResult)) {
      $suffixes[] = $dbRow['s'];
    }
    $len = count($suffixes);

    $lexemArray = array();
    foreach ($suffixes as $i => $suffix) {
      // Load the two most frequent and two least frequent suffixes, or all
      // of them if there are less than 4.
      if ($i <= 1 || $i >= $len - 2) {
        $lexemArray[] = Lexem::loadByCanonicalModelSuffix($modelType,
                                                          $model->number,
                                                          $suffixes[$i]);
      }
    }
    $lexems[] = $lexemArray;

    // Now for each lexem in $lexemArray, load the list of inflections.
    $wlMapArray = array();
    foreach ($lexemArray as $l) {
      $wlMapArray[] = WordList::loadByLexemIdMapByInflectionId($l->id);
    }
    $wlMaps[] = $wlMapArray;
  }

  smarty_assign('modelType', $modelType);
  smarty_assign('models', $models);
  smarty_assign('lexems', $lexems);
  smarty_assign('wlMaps', $wlMaps);
}

smarty_assign('modelTypes', ModelType::loadCanonical());
smarty_assign('sectionTitle', 'Verificare paradigme');
smarty_assign('recentLinks', RecentLink::loadForUser());
smarty_displayWithoutSkin('flex/verifyParadigms.ihtml');

?>
