<?php
require_once("../../phplib/util.php"); 
util_assertModerator(PRIV_LOC);
util_assertNotMirror();

$updateModels = util_getRequestParameter('updateModels');
$modelType = util_getRequestParameter('modelType');

if ($updateModels) {
  foreach ($_REQUEST as $name => $value) {
    if (text_startsWith($name, 'model_') && $value != '0') {
      $parts = preg_split('/_/', $name, 2);
      assert($parts[0] == 'model');
      $modelNumber = str_replace('_', '.', $parts[1]); // stupid PHP replaces . with _ in incoming data

      // If given, add a comment to the lexems that were shown for this model.
      $comment = util_getRequestParameter("com_$modelNumber");
      if ($comment) {
        $lexemIds = util_getRequestParameter("lexems_$modelNumber");
        foreach ($lexemIds as $lexemId) {
          $lexem = Lexem::get("id = {$lexemId}");
          $lexem->comment = $comment;
          $lexem->save();
        }
      }

      // Now mark this model with the flag value.
      $m = Model::get("modelType = '{$modelType}' and number = '{$modelNumber}'");
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
  $ifMaps = array();

  foreach ($models as $model) {
    // Load the distinct 3-letter suffixes, in descending order of the frequency.
    $dbResult = db_execute("select substring(reverse, 1, 3) as s from Lexem, ModelType where modelType = code and canonical = '{$modelType}' " .
                           "and modelNumber = '{$model->number}' group by s order by count(*) desc");
    $suffixes = array();
    while (!$dbResult->EOF) {
      $suffixes[] = $dbResult->fields['s'];
      $dbResult->MoveNext();
    }
    $len = count($suffixes);

    $lexemArray = array();
    foreach ($suffixes as $i => $suffix) {
      // Load the two most frequent and two least frequent suffixes, or all
      // of them if there are less than 4.
      if ($i <= 1 || $i >= $len - 2) {
        $dbResult = db_execute("select Lexem.* from Lexem, ModelType where modelType = code and canonical = '{$modelType}' " .
                               "and modelNumber = '{$model->number}' and reverse like '{$suffixes[$i]}%' order by form desc limit 1");
        $tmp = db_getObjects(new Lexem(), $dbResult);
        $lexemArray[] = $tmp[0];
      }
    }
    $lexems[] = $lexemArray;

    // Now for each lexem in $lexemArray, load the list of inflections.
    $ifMapArray = array();
    foreach ($lexemArray as $l) {
      $ifMapArray[] = InflectedForm::loadByLexemIdMapByInflectionId($l->id);
    }
    $ifMaps[] = $ifMapArray;
  }

  smarty_assign('modelType', $modelType);
  smarty_assign('models', $models);
  smarty_assign('lexems', $lexems);
  smarty_assign('ifMaps', $ifMaps);
}

smarty_assign('modelTypes', ModelType::loadCanonical());
smarty_assign('sectionTitle', 'Verificare paradigme');
smarty_assign('recentLinks', RecentLink::loadForUser());
smarty_displayWithoutSkin('flex/verifyParadigms.ihtml');

?>
