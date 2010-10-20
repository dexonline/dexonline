<?
require_once("../../phplib/util.php"); 
ini_set('memory_limit', '512M');
ini_set('max_execution_time', '3600');
util_assertModerator(PRIV_LOC);
util_assertNotMirror();
debug_off();

$modelType = util_getRequestParameter('modelType');
$modelNumber = util_getRequestParameter('modelNumber');
$previewButton = util_getRequestParameter('previewButton');
$confirmButton = util_getRequestParameter('confirmButton');

$inflections = db_find(new Inflection(), "modelType = '{$modelType}' order by rank");
// Load the original data
$model = Model::get("modelType = '{$modelType}' and number = '{$modelNumber}'");
$exponent = $model->exponent;
$lexem = new Lexem($exponent, $modelType, $modelNumber, '');
$ifs = $lexem->generateParadigm();
$mdMap = ModelDescription::getByModelIdMapByInflectionIdVariantApplOrder($model->id);
$forms = array();
foreach ($inflections as $infl) {
  $forms[$infl->id] = array();
}
foreach ($ifs as $if) {
  $forms[$if->inflectionId][] = array('form' => $if->form, 'isLoc' => $mdMap[$if->inflectionId][$if->variant][0]->isLoc);
}

$participleNumber = ($modelType == 'V') ? ParticipleModel::loadByVerbModel($modelNumber)->adjectiveModel : '';

if ($previewButton || $confirmButton) {
  // Load the new forms and exponent;
  $newModelNumber = util_getRequestParameter('newModelNumber');
  $newExponent = util_getRequestParameter('newExponent');
  $newDescription = util_getRequestParameter('newDescription');
  $newParticipleNumber = util_getRequestParameter('newParticipleNumber');
  $newForms = array();
  foreach ($inflections as $infl) {
    $newForms[$infl->id] = array();
  }
  readRequest($newForms);
} else {
  $newModelNumber = $modelNumber;
  $newExponent = $exponent;
  $newDescription = $model->description;
  $newParticipleNumber = $participleNumber;
  $newForms = $forms;
}

$exponentAccentAdded = ($exponent != $newExponent &&
                        str_replace("'", '', $newExponent) == $exponent);
$exponentChanged = ($exponent != $newExponent && !$exponentAccentAdded);

$errorMessage = array();
if ($newModelNumber != $modelNumber) {
  // Disallow duplicate model numbers
  $m = Model::loadCanonicalByTypeNumber($modelType, $newModelNumber);
  if ($m) {
    $errorMessage[] = "Modelul $modelType$newModelNumber există deja.";
  }
}

if ($previewButton || $confirmButton) {
  // Compare the old and new lists. Extract the transforms where needed.
  $isPronoun = ($modelType == 'P');
  $regenTransforms = array();
  // Recalculate transforms when
  // (1) the form list has changed OR
  // (2) an accent was added in the exponent and some forms contain accents OR
  // (3) the exponent changed (other than by adding an accent)
  // We do NOT do anything when the isLoc values change.
  foreach ($inflections as $infl) {
    if (!equalArrays($forms[$infl->id], $newForms[$infl->id]) ||
        $exponentAccentAdded && anyAccents($newForms[$infl->id]) ||
        $exponentChanged) {
      $regenTransforms[$infl->id] = array();
      foreach ($newForms[$infl->id] as $tuple) {
        $transforms = text_extractTransforms($newExponent, $tuple['form'], $isPronoun);
        if ($transforms) {
          $regenTransforms[$infl->id][] = $transforms;
        } else {
          $errorMessage[] = "Nu pot extrage transformările între $newExponent și " . htmlentities($tuple['form']) . ".";
        }
      }
    }
  }
  
  // Now load the affected lexems. For each lexem, inflection and transform
  // list, generate a new form.
  $lexems = Lexem::loadByCanonicalModel($modelType, $modelNumber);
  $regenForms = array();
  foreach ($lexems as $l) {
    $regenRow = array();
    foreach ($regenTransforms as $inflId => $variants) {
      $regenRow[$inflId] = array();
      foreach ($variants as $transforms) {
        $accentShift = array_pop($transforms);
        if ($accentShift != UNKNOWN_ACCENT_SHIFT &&
            $accentShift != NO_ACCENT_SHIFT) {
          $accentedVowel = array_pop($transforms);
        } else {
          $accentedVowel = '';
        }
        $result = text_applyTransforms($l->form, $transforms, $accentShift, $accentedVowel);
        $regenRow[$inflId][] = $result;
        if (!$result && count($errorMessage) <= 20) {
          $errorMessage[] = "Nu pot calcula una din formele lexemului " . htmlentities($l->form) . ".";
        }
      }
    }
    $regenForms[] = $regenRow;
  }

  // Now load the affected adjectives if the participle model changed
  if ($participleNumber != $newParticipleNumber) {
    $participleParadigms = array();
    $participles = loadParticiplesForVerbModel($modelNumber, $participleNumber);
    foreach ($participles as $p) {
      $p->modelNumber = $newParticipleNumber;
      $ifs = $p->generateParadigm();
      if (is_array($ifs)) {
        $participleParadigms[] = InflectedForm::mapByInflectionRank($ifs);
      } else {
        $errorMessage[] = "Nu pot declina participiul \"".htmlentities($p->form)."\" " .
          "conform modelului A$newParticipleNumber.";
        $participleParadigms[] = null;
      }
    }

    smarty_assign('participles', $participles);
    smarty_assign('participleParadigms', $participleParadigms);
  }

  if ($confirmButton) {
    // Save the transforms and model descriptions
    foreach ($regenTransforms as $inflId => $transformMatrix) {
      db_execute("delete from ModelDescription where modelId = {$model->id} and inflectionId = {$inflId}");
      $variant = 0;
      foreach ($transformMatrix as $transforms) {
        $accentShift = array_pop($transforms);
        if ($accentShift != UNKNOWN_ACCENT_SHIFT && $accentShift != NO_ACCENT_SHIFT) {
          $accentedVowel = array_pop($transforms);
        } else {
          $accentedVowel = '';
        }

        $order = 0;
        $mds = array();
        for ($i = count($transforms) - 1; $i >= 0; $i--) {
          $t = $transforms[$i];
          // Make sure the transform has an ID.
          $t = Transform::createOrLoad($t->transfFrom, $t->transfTo);
          $md = new ModelDescription();
          $md->modelId = $model->id;
          $md->inflectionId = $inflId;
          $md->variant = $variant;
          $md->applOrder = $order++;
          $md->isLoc = false;
          $md->transformId = $t->id;
          $md->accentShift = $accentShift;
          $md->vowel = $accentedVowel;
          $md->save();
        }
        $variant++;
      }
    }

    // Set the isLoc bits appropriately
    foreach ($newForms as $inflId => $tupleArray) {
      foreach ($tupleArray as $variant => $tuple) {
        $md = ModelDescription::get("modelId = {$model->id} and inflectionId = {$inflId} and variant = {$variant} and applOrder = 0");
        $md->isLoc = $tuple['isLoc'];
        $md->save();
      }
    }

    // Regenerate the affected inflections for every lexem
    if (count($regenTransforms)) {
      foreach ($lexems as $l) {
        $l->modDate = time();
        $l->save();
        $l->regenerateParadigm();
      }
    }

    if ($modelNumber != $newModelNumber) {
      if ($modelType == 'V') {
        $oldPm = ParticipleModel::loadByVerbModel($modelNumber);
        $oldPm->verbModel = $newModelNumber;
        $oldPm->save();
      } else if ($modelType == 'A') {
        // Update all participle models that use this adjective model
        db_execute("update ParticipleModel set adjectivModel = '%s' where adjectivModel = '%s'", addslashes($newModelNumber), addslashes($modelNumber));
      }

      foreach ($lexems as $l) {
        if ($l->modelNumber == $modelNumber) {
          $l->modelNumber = $newModelNumber;
          $l->save();
        }
      }
    }

    if ($participleNumber != $newParticipleNumber) {
      $pm = ParticipleModel::loadByVerbModel($newModelNumber);
      $pm->adjectiveModel = $newParticipleNumber;
      $pm->save();

      $participles = loadParticiplesForVerbModel($modelNumber, $participleNumber);
      foreach ($participles as $p) {
        $p->modelNumber = $newParticipleNumber;
        $p->save();
        $p->regenerateParadigm();
      }
    }

    $model->exponent = $newExponent;
    $model->description = $newDescription;
    $model->number = $newModelNumber;
    $model->save();
    util_redirect('../admin/index.php');
  }

  smarty_assign('lexems', $lexems);
  smarty_assign('regenForms', $regenForms);
  smarty_assign('regenTransforms', $regenTransforms);
}

if ($modelType == 'V') {
  smarty_assign('adjModels', Model::loadByType('A'));
}

$inputValues = array();
foreach ($inflections as $infl) {
  $inputValues[$infl->id] = array();
  foreach ($newForms[$infl->id] as $form) {
    $inputValues[$infl->id][] = array("form" => $form, "isLoc" => 1);
  }
}

if (!$previewButton && !$confirmButton) {
  RecentLink::createOrUpdate("Editare model: {$model}");
}

smarty_assign('inflections', $inflections);
smarty_assign('inflectionMap', Inflection::mapById($inflections));
smarty_assign('modelType', $modelType);
smarty_assign('modelNumber', $modelNumber);
smarty_assign('newModelNumber', $newModelNumber);
smarty_assign('exponent', $exponent);
smarty_assign('newExponent', $newExponent);
smarty_assign('description', $model->description);
smarty_assign('newDescription', $newDescription);
smarty_assign('participleNumber', $participleNumber);
smarty_assign('newParticipleNumber', $newParticipleNumber);
smarty_assign('newForms', $newForms);
smarty_assign('inputValues', $inputValues);
smarty_assign('recentLinks', RecentLink::loadForUser());
smarty_assign('wasPreviewed', $previewButton);
smarty_assign('errorMessage', $errorMessage);
smarty_displayWithoutSkin('flex/editModel.ihtml');


/****************************************************************************/

/**
 * $a, $b: arrays of ($form, $isLoc) tuples. Only compares the forms.
 */
function equalArrays($a, $b) {
  if (count($a) != count($b)) {
    return false;
  }

  foreach ($a as $key => $tuple) {
    if ($a[$key]['form'] != $b[$key]['form']) {
      return false;
    }
  }

  return true;
}

/**
 * $v: an array of ($form, $isLoc) tuples.
 */
function anyAccents($v) {
  foreach ($v as $tuple) {
    if (mb_strpos($tuple['form'], "'")) {
      return true;
    }
  }
  return false;
}

// Assumes that $participleNumber is the correct participle (adjective) model for $modelNumber.
function loadParticiplesForVerbModel($modelNumber, $participleNumber) {
  $infl = Inflection::loadParticiple();
  $dbResult = db_execute("select part.* from Lexem part, InflectedForm i, Lexem infin where infin.modelType = 'VT' and infin.modelNumber = '$modelNumber' " .
                         "and i.lexemId = infin.id and i.inflectionId = {$infl->id} and part.formNoAccent = i.formNoAccent and part.modelType = 'A' " .
                         "and part.modelNumber = '$participleNumber' order by part.formNoAccent");
  return db_getObjects(new Lexem(), $dbResult);
}

/**
 * Read forms and isLoc checkboxes from the request.
 * The map is already populated with all the applicable inflection IDs.
 * InflectionId's and variants are coded in the request parameters.
 **/
function readRequest(&$map) {
  foreach ($_REQUEST as $name => $value) {
    if (text_startsWith($name, 'forms_')) {
      $parts = preg_split('/_/', $name);
      assert(count($parts) == 3);
      assert($parts[0] == 'forms');
      $inflId = $parts[1];
      $variant = $parts[2];
      $form = trim($value);
      if ($form) {
        $map[$inflId][$variant] = array('form' => $form, 'isLoc' => false);
      }
    } else if (text_startsWith($name, 'isLoc_')) {
      $parts = preg_split('/_/', $name);
      assert(count($parts) == 3);
      assert($parts[0] == 'isLoc');
      $inflId = $parts[1];
      $variant = $parts[2];
      if (array_key_exists($variant, $map[$inflId])) {
        $map[$inflId][$variant]['isLoc'] = true;
      }
    }
  }

  // Now reindex the array, in case the admin left, for example, variant 1 empty but filled in variant 2.
  foreach ($map as $inflId => $variants) {
    $map[$inflId] = array_values($variants);
  }
}

?>
