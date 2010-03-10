<?
require_once("../../phplib/util.php"); 
ini_set('max_execution_time', '3600');
util_assertFlexModeratorStatus();
util_assertNotMirror();
debug_off();

$modelType = util_getRequestParameter('modelType');
$modelNumber = util_getRequestParameter('modelNumber');
$previewButton = util_getRequestParameter('previewButton');
$confirmButton = util_getRequestParameter('confirmButton');

$inflections = Inflection::loadByModelType($modelType);
// Load the original data
$model = Model::loadByTypeNumber($modelType, $modelNumber);
$exponent = $model->exponent;
$lexem = Lexem::create($exponent, $modelType, $modelNumber, '');
$ifs = $lexem->generateParadigm();
$forms = array();
foreach ($inflections as $infl) {
  $forms[$infl->id] = array();
}
foreach($ifs as $if) {
  $forms[$if->inflectionId][] = $if->form;
}

$participleNumber = ($modelType == 'V')
  ? ParticipleModel::loadByVerbModel($modelNumber)->adjectiveModel
  : '';

if ($previewButton || $confirmButton) {
  // Load the new forms and exponent;
  $newModelNumber = util_getRequestParameter('newModelNumber');
  $newExponent = util_getRequestParameter('newExponent');
  $newDescription = util_getRequestParameter('newDescription');
  $newParticipleNumber = util_getRequestParameter('newParticipleNumber');
  $newForms = array();
  foreach ($_REQUEST as $name => $value) {
    if (text_startsWith($name, 'forms_')) {
      $formArray = array();
      $parts = split('_', $name);
      assert(count($parts) == 2);
      assert($parts[0] == 'forms');
      $inflId = $parts[1];
      $parts = split(',', $value);
      foreach ($parts as $form) {
        $form = trim($form);
        if ($form) {
          $formArray[] = $form;
        }
      }
      $newForms[$inflId] = $formArray;
    }
  }
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
  foreach ($inflections as $infl) {
    if (!equalArrays($forms[$infl->id], $newForms[$infl->id]) ||
        $exponentAccentAdded && anyAccents($newForms[$infl->id]) ||
        $exponentChanged) {
      $regenTransforms[$infl->id] = array();
      foreach ($newForms[$infl->id] as $form) {
        $transforms = text_extractTransforms($newExponent, $form, $isPronoun);
        if ($transforms) {
          $regenTransforms[$infl->id][] = $transforms;
        } else {
          $errorMessage[] = "Nu pot extrage transformările între " .
            "$newExponent și ".htmlentities($form).".";
          
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
        $result = text_applyTransforms($l->form, $transforms, $accentShift,
                                       $accentedVowel);
        $regenRow[$inflId][] = $result;
        if (!$result && count($errorMessage) <= 20) {
          $errorMessage[] = "Nu pot calcula una din formele lexemului " .
            htmlentities($l->form).".";
        }
      }
    }
    $regenForms[] = $regenRow;
  }

  // Now load the affected adjectives if the participle model changed
  if ($participleNumber != $newParticipleNumber) {
    $participleParadigms = array();
    $participles = Lexem::loadParticiplesForVerbModel($modelNumber,
                                                      $participleNumber);
    foreach ($participles as $p) {
      $p->modelNumber = $newParticipleNumber;
      $ifs = $p->generateParadigm();
      if (is_array($ifs)) {
        $participleParadigms[] = InflectedForm::mapByInflectionId($ifs);
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
      // Delete ModelDescriptions for the model and inflId, for all variants
      ModelDescription::deleteByModelInflection($model->id, $inflId);
      $formArray = $newForms[$inflId];
      $variant = 0;
      foreach ($transformMatrix as $i => $transforms) {
        $form = $formArray[$i];
        $accentShift = array_pop($transforms);
        if ($accentShift != UNKNOWN_ACCENT_SHIFT &&
            $accentShift != NO_ACCENT_SHIFT) {
          $accentedVowel = array_pop($transforms);
        } else {
          $accentedVowel = '';
        }

        $order = 0;
        for ($i = count($transforms) - 1; $i >= 0; $i--) {
          $t = $transforms[$i];
          // Make sure the transform has an ID.
          $t = Transform::createOrLoad($t->transfFrom, $t->transfTo);
          $md = ModelDescription::create($model->id, $inflId, $variant, $order, $t->id, $accentShift, $accentedVowel);
          $md->save();
          $order++;
        }
        $variant++;
      }
    }

    // Regenerate the affected inflections for every lexem
    if (count($regenTransforms)) {
      foreach ($lexems as $l) {
        $l->updateModDate();
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

      $participles = Lexem::loadParticiplesForVerbModel($modelNumber,
                                                        $participleNumber);
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
    exit;
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
  $inputValues[] = join(', ', $newForms[$infl->id]);
}

if (!$previewButton && !$confirmButton) {
  RecentLink::createOrUpdate('Editare model: ' . $model->getName());
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

function equalArrays($a, $b) {
  if (count($a) != count($b)) {
    return false;
  }

  foreach ($a as $key => $value) {
    if ($b[$key] != $value) {
      return false;
    }
  }

  return true;
}

function anyAccents($strArray) {
  foreach ($strArray as $s) {
    if (mb_strpos($s, "'")) {
      return true;
    }
  }
  return false;
}

?>
