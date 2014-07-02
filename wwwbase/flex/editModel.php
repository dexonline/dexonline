<?php
require_once("../../phplib/util.php"); 
ini_set('memory_limit', '512M');
ini_set('max_execution_time', '3600');
util_assertModerator(PRIV_LOC);
util_assertNotMirror();
DebugInfo::disable();

$modelType = util_getRequestParameter('modelType');
$modelNumber = util_getRequestParameter('modelNumber');
$previewButton = util_getRequestParameter('previewButton');
$confirmButton = util_getRequestParameter('confirmButton');

$modelType = ModelType::canonicalize($modelType);

$inflections = Model::factory('Inflection')->where('modelType', $modelType)->order_by_asc('rank')->find_many();
// Load the original data
$model = FlexModel::get_by_modelType_number($modelType, $modelNumber);
$exponent = $model->exponent;
$lexem = Lexem::deepCreate($exponent, $modelType, $modelNumber);
$ifs = $lexem->getLexemModels()[0]->generateInflectedForms();
$mdMap = ModelDescription::getByModelIdMapByInflectionIdVariantApplOrder($model->id);
$forms = array();
foreach ($inflections as $infl) {
  $forms[$infl->id] = array();
}
foreach ($ifs as $if) {
  $forms[$if->inflectionId][] = array('form' => $if->form,
                                      'isLoc' => $mdMap[$if->inflectionId][$if->variant][0]->isLoc,
                                      'recommended' => $mdMap[$if->inflectionId][$if->variant][0]->recommended);
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
  $m = FlexModel::loadCanonicalByTypeNumber($modelType, $newModelNumber);
  if ($m) {
    $errorMessage[] = "Modelul {$modelType}{$newModelNumber} există deja.";
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
  // We do propagate the change to all InflectedForms when the values for recommended change.
  foreach ($inflections as $infl) {
    if (!equalArrays($forms[$infl->id], $newForms[$infl->id]) ||
        $exponentAccentAdded && anyAccents($newForms[$infl->id]) ||
        $exponentChanged) {
      $regenTransforms[$infl->id] = array();
      foreach ($newForms[$infl->id] as $tuple) {
        $transforms = FlexStringUtil::extractTransforms($newExponent, $tuple['form'], $isPronoun);
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
  $lexemModels = LexemModel::loadByCanonicalModel($modelType, $modelNumber);
  $regenForms = array();
  foreach ($lexemModels as $lm) {
    $l = $lm->getLexem();
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
        $result = FlexStringUtil::applyTransforms($l->form, $transforms, $accentShift, $accentedVowel);
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
    $participles = loadParticiplesForVerbModel($modelNumber, $participleNumber);
    foreach ($participles as $p) {
      $p->modelNumber = $newParticipleNumber;
      $ifs = $p->generateInflectedFormMap();
      if (!is_array($ifs)) {
        $errorMessage[] = sprintf('Nu pot declina participiul "%s" conform modelului A%s.',
                                  htmlentities($p->getLexem()->form), $newParticipleNumber);
      }
    }

    SmartyWrap::assign('participles', $participles);
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
          $md = Model::factory('ModelDescription')->create();
          $md->modelId = $model->id;
          $md->inflectionId = $inflId;
          $md->variant = $variant;
          $md->applOrder = $order++;
          $md->isLoc = false;
          $md->recommended = false;
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
        $md = Model::factory('ModelDescription')->where('modelId', $model->id)->where('inflectionId', $inflId)->where('variant', $variant)
          ->where('applOrder', 0)->find_one();
        $md->isLoc = $tuple['isLoc'];
        $md->recommended = $tuple['recommended'];
        $md->save();
      }
    }

    // Set the recommended bits appropriately
    foreach ($newForms as $inflId => $tupleArray) {
      foreach ($tupleArray as $variant => $tuple) {
        $recommended = intval($tuple['recommended']);
        db_execute("update InflectedForm i, LexemModel lm, Lexem l, Model m, ModelType mt " .
                   "set i.recommended = {$recommended} where i.lexemModelId = lm.id and lm.lexemId = l.id and lm.modelType = mt.code " .
                   "and mt.canonical = m.modelType and lm.modelNumber = m.number and m.id = {$model->id} and i.inflectionId = {$inflId} and variant = {$variant}");
      }
    }

    // Regenerate the affected inflections for every lexem
    if (count($regenTransforms)) {
      foreach ($lexemModels as $lm) {
        $lm->regenerateParadigm();
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

      foreach ($lexemModels as $lm) {
        if ($lm->modelNumber == $modelNumber) {
          $lm->modelNumber = $newModelNumber;
          $lm->save();
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

  SmartyWrap::assign('lexemModels', $lexemModels);
  SmartyWrap::assign('regenForms', $regenForms);
  SmartyWrap::assign('regenTransforms', $regenTransforms);
}

if ($modelType == 'V') {
  SmartyWrap::assign('adjModels', FlexModel::loadByType('A'));
}

$inputValues = array();
foreach ($inflections as $infl) {
  $inputValues[$infl->id] = array();
  foreach ($newForms[$infl->id] as $form) {
    $inputValues[$infl->id][] = array('form' => $form, 'isLoc' => 1, 'recommended' => 1);
  }
}

if (!$previewButton && !$confirmButton) {
  RecentLink::createOrUpdate("Editare model: {$model}");
}

SmartyWrap::assign('inflections', $inflections);
SmartyWrap::assign('inflectionMap', Inflection::mapById($inflections));
SmartyWrap::assign('modelType', $modelType);
SmartyWrap::assign('adjModelType', ModelType::get_by_code('A'));
SmartyWrap::assign('modelNumber', $modelNumber);
SmartyWrap::assign('newModelNumber', $newModelNumber);
SmartyWrap::assign('exponent', $exponent);
SmartyWrap::assign('newExponent', $newExponent);
SmartyWrap::assign('description', $model->description);
SmartyWrap::assign('newDescription', $newDescription);
SmartyWrap::assign('participleNumber', $participleNumber);
SmartyWrap::assign('newParticipleNumber', $newParticipleNumber);
SmartyWrap::assign('newForms', $newForms);
SmartyWrap::assign('inputValues', $inputValues);
SmartyWrap::assign('recentLinks', RecentLink::loadForUser());
SmartyWrap::assign('wasPreviewed', $previewButton);
SmartyWrap::assign('errorMessage', $errorMessage);
SmartyWrap::assign('sectionTitle', "Editare model {$modelType}{$modelNumber}");
SmartyWrap::addCss('paradigm', 'jqueryui');
SmartyWrap::addJs('jquery', 'jqueryui');
SmartyWrap::displayAdminPage('flex/editModel.ihtml');


/****************************************************************************/

/**
 * $a, $b: arrays of ($form, $isLoc, $recommended) tuples. Only compares the forms.
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
 * $v: an array of ($form, $isLoc, $recommended) tuples.
 */
function anyAccents($v) {
  foreach ($v as $tuple) {
    if (mb_strpos($tuple['form'], "'")) {
      return true;
    }
  }
  return false;
}

// Returns all LexemModels of model A$participleNumber that belong to lexems having the same form
// as participle InflectedForms of verbs of model VT$modelNumber.
// Assumes that $participleNumber is the correct participle (adjective) model for $modelNumber.
function loadParticiplesForVerbModel($modelNumber, $participleNumber) {
  $infl = Inflection::loadParticiple();
  return Model::factory('LexemModel')
    ->table_alias('lmpart')
    ->join('Lexem', 'lmpart.lexemId = part.id', 'part')
    ->join('InflectedForm', 'part.formNoAccent = i.formNoAccent', 'i')
    ->join('LexemModel', 'i.lexemModelId = lminfin.id', 'lminfin')
    ->select('lmpart.*')
    ->where('lminfin.modelType', 'VT')
    ->where('lminfin.modelNumber', $modelNumber)
    ->where('i.inflectionId', $infl->id)
    ->where('lmpart.modelType', 'A')
    ->where('lmpart.modelNumber', $participleNumber)
    ->order_by_asc('part.formNoAccent')
    ->find_many();
}

/**
 * Read forms and isLoc/recommended checkboxes from the request.
 * The map is already populated with all the applicable inflection IDs.
 * InflectionId's and variants are coded in the request parameters.
 **/
function readRequest(&$map) {
  foreach ($_REQUEST as $name => $value) {
    if (StringUtil::startsWith($name, 'forms_')) {
      $parts = preg_split('/_/', $name);
      assert(count($parts) == 3);
      assert($parts[0] == 'forms');
      $inflId = $parts[1];
      $variant = $parts[2];
      $form = trim($value);
      if ($form) {
        $map[$inflId][$variant] = array('form' => $form, 'isLoc' => false, 'recommended' => false);
      }
    } else if (StringUtil::startsWith($name, 'isLoc_')) {
      $parts = preg_split('/_/', $name);
      assert(count($parts) == 3);
      assert($parts[0] == 'isLoc');
      $inflId = $parts[1];
      $variant = $parts[2];
      if (array_key_exists($variant, $map[$inflId])) {
        $map[$inflId][$variant]['isLoc'] = true;
      }
    } else if (StringUtil::startsWith($name, 'recommended_')) {
      $parts = preg_split('/_/', $name);
      assert(count($parts) == 3);
      assert($parts[0] == 'recommended');
      $inflId = $parts[1];
      $variant = $parts[2];
      if (array_key_exists($variant, $map[$inflId])) {
        $map[$inflId][$variant]['recommended'] = true;
      }
    }
  }

  // Now reindex the array, in case the admin left, for example, variant 1 empty but filled in variant 2.
  foreach ($map as $inflId => $variants) {
    $map[$inflId] = array_values($variants);
  }
}

?>
