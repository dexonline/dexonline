<?php
require_once("../../phplib/util.php");
assert_options(ASSERT_BAIL, 1);
debug_off();

$migrateAll = false;
for ($i = 1; $i < count($argv); $i++) {
  $arg = $argv[$i];
  if ($arg == "-a") {
    $migrateAll = true;
  } else {
    OS::errorAndExit("Unknown flag: $arg");
  }
}

if ($migrateAll) {
  mysql_query("delete from transforms where transf_from != ''" .
              "or transf_to != ''");
}

$query = $migrateAll
  ? "select * from models where model_type not in ('I', 'T')"
  : "select models.* from models left outer join model_description " .
  "on model_id = md_model where md_model is null";
$dbResult = logged_query($query);
$numModels = 0;

while ($dbRow = mysql_fetch_assoc($dbResult)) {
  $model = new Model();
  $model->populateFromDbRow($dbRow);
  //print "{$model->modelType} {$model->number}\n";

  // Load all the DMLR model records
  mysql_query("delete from model_description where md_model = {$model->id}");
  $query = "select form, infl_id, variant, is_baseform from dmlr_models " .
    "where model_type = '{$model->modelType}' " .
    "and model_no = '" . addslashes($model->number) . "' order by infl_id";
  $dmlrDbResult = logged_query($query);
  $results = db_getArray($dmlrDbResult);

  $baseForm = null;
  foreach ($results as $row) {
    $form = $row['form'];
    $variant = $row['variant'];
    $inflId = $row['infl_id'];
    $isBaseForm = $row['is_baseform'];

    if ($baseForm && $isBaseForm) {
      die("Incorrect baseform for {$model->modelType}{$model->number}\n");
    }

    if (!$baseForm) {
      $baseForm = $form;
    }

    if (text_contains($baseForm, "'") ^ text_contains($form, "'")) {
      print "Incomplete accents for $baseForm => $form\n";
    }

    //print "$baseForm=>$form\n";
    if (!text_validateAlphabet($form, "aăâbcdefghiîjklmnopqrsștțuvwxyz'")) {
      die("Illegal characters in form $form\n");
    }
    $transforms = text_extractTransforms($baseForm, $form,
                                         $model->modelType == 'P');
    assert(count($transforms) >= 2);
    // Split off the last transform: it indicates the accent shift
    $accentShift = array_pop($transforms);
    if ($accentShift != UNKNOWN_ACCENT_SHIFT &&
        $accentShift != NO_ACCENT_SHIFT) {
      $accentedVowel = array_pop($transforms);
    } else {
      $accentedVowel = '';
    }

    //foreach ($transforms as $t) {
    //  print $t->toString() . ' ';
    //}
    //print "$accentShift\n";

    // Reverse the transforms array. At the same time, save the transforms.
    $newT = array();
    for ($i = count($transforms) - 1; $i >= 0; $i--) {
      $t = $transforms[$i];
      $newT[] = Transform::createOrLoad($t->from, $t->to);
    }
    $transforms = $newT;

    foreach ($transforms as $i => $t) {
      $md = ModelDescription::create($model->id, $inflId, $variant,
                                     $i, $t->id, $accentShift, $accentedVowel);
      $md->save();
    }
  }
  $numModels++;
}

print "$numModels models migrated from dmlr_models.\n";

?>
