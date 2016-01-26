<?php
require_once("../../phplib/util.php"); 
ini_set('memory_limit', '512M');
ini_set('max_execution_time', '3600');
util_assertModerator(PRIV_LOC);
util_assertNotMirror();
DebugInfo::disable();

define('SHORT_LIST_LIMIT', 10);

$id = util_getRequestParameter('id');
$previewButton = util_getRequestParameter('previewButton');
$confirmButton = util_getRequestParameter('confirmButton');
$shortList = util_getBoolean('shortList');

$m = FlexModel::get_by_id($id);
$pm = ParticipleModel::loadForModel($m);
$inflections = Model::factory('Inflection')
             ->where('modelType', $m->modelType)
             ->order_by_asc('rank')
             ->find_many();

// Generate the forms
$lexem = Lexem::deepCreate($m->exponent, $m->modelType, $m->number);
$ifs = $lexem->getFirstLexemModel()->generateInflectedForms();

// Load the model descriptions
$mds = ModelDescription::loadForModel($m->id);

// Map the forms by inflectionId and variant. Include inflectionId's that
// the current model doesn't have
$forms = [];
foreach ($inflections as $infl) {
  $forms[$infl->id] = [];
}
foreach ($ifs as $i => $if) {
  // This works because $ifs and $mds are both ordered by inflectionId,
  // then by variant
  $inflId = $if->inflectionId;
  $forms[$inflId][] = ['form' => $if->form,
                       'isLoc' => $mds[$i]->isLoc,
                       'recommended' => $mds[$i]->recommended];
}

$errorMessage = [];

if (!$previewButton && !$confirmButton) {
  // just viewing the page
  RecentLink::createOrUpdate("Editare model: {$m}");
  SmartyWrap::assign('m', $m);
  SmartyWrap::assign('pm', $pm);
  SmartyWrap::assign('forms', $forms);

} else {
  // Preview or Save button pressed
  // Read form values
  $nm = FlexModel::get_by_id($id); // new model
  $nm->number = util_getRequestParameter('number');
  $nm->description = util_getRequestParameter('description');
  $nm->exponent = util_getRequestParameter('exponent');

  $npm = ParticipleModel::loadForModel($m);
  if ($npm) {
    $npm->adjectiveModel = util_getRequestParameter('participleNumber');
  }

  $nforms = [];
  foreach ($inflections as $infl) {
    $nforms[$infl->id] = [];
  }
  readRequest($nforms);

  // calculate transforms
  if ($nm->number != $m->number) {
    // disallow duplicate model numbers
    $dup = FlexModel::loadCanonicalByTypeNumber($nm->modelType, $nm->number);
    if ($dup) {
      $errorMessage[] = "Modelul {$nm} există deja.";
    }
  }

  // Compare the old and new lists. Extract the transforms where needed.
  $isPronoun = ($nm->modelType == 'P');
  $regenTransforms = [];
  // Recalculate transforms when either the form list or the exponent change.
  // Do nothing when the isLoc values change.
  foreach ($inflections as $infl) {
    if (!equalArrays($forms[$infl->id], $nforms[$infl->id]) ||
        $m->exponent != $nm->exponent) {
      $regenTransforms[$infl->id] = [];
      foreach ($nforms[$infl->id] as $tuple) {
        $transforms = FlexStringUtil::extractTransforms($nm->exponent, $tuple['form'], $isPronoun);
        if ($transforms) {
          $regenTransforms[$infl->id][] = $transforms;
        } else {
          $errorMessage[] = "Nu pot extrage transformările între {$nm->exponent} și " . htmlentities($tuple['form']) . ".";
        }
      }
    }
  }
  
  // Load the affected lexems. For each lexem, inflection and transform
  // list, generate a new form.
  $limit = ($shortList && !$confirmButton) ? SHORT_LIST_LIMIT : 0;
  $lexemModels = LexemModel::loadByCanonicalModel($m->modelType, $m->number, $limit);
  $regenForms = [];
  foreach ($lexemModels as $lm) {
    $l = $lm->getLexem();
    $regenRow = [];
    foreach ($regenTransforms as $inflId => $variants) {
      $regenRow[$inflId] = [];
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
  if ($pm && ($pm->adjectiveModel != $npm->adjectiveModel)) {
    $participles = loadParticiplesForVerbModel($m, $pm);
    foreach ($participles as $p) {
      $p->modelNumber = $npm->adjectiveModel;
      $ifs = $p->generateInflectedFormMap();
      if (!is_array($ifs)) {
        $errorMessage[] = sprintf('Nu pot declina participiul "%s" conform modelului A%s.',
                                  htmlentities($p->getLexem()->form), $npm->adjectiveModel);
      }
    }

    SmartyWrap::assign('participles', $participles);
  }

  if ($confirmButton) {
    // Save the transforms and model descriptions
    foreach ($regenTransforms as $inflId => $transformMatrix) {
      ModelDescription::delete_all_by_modelId_inflectionId($m->id, $inflId);
      foreach ($transformMatrix as $variant => $transforms) {
        $accentShift = array_pop($transforms);
        if ($accentShift != UNKNOWN_ACCENT_SHIFT && $accentShift != NO_ACCENT_SHIFT) {
          $accentedVowel = array_pop($transforms);
        } else {
          $accentedVowel = '';
        }

        $order = count($transforms);
        foreach ($transforms as $t) {
          // Make sure the transform has an ID.
          $t = Transform::createOrLoad($t->transfFrom, $t->transfTo);
          $md = Model::factory('ModelDescription')->create();
          $md->modelId = $m->id;
          $md->inflectionId = $inflId;
          $md->variant = $variant;
          $md->applOrder = --$order;
          $md->isLoc = false;
          $md->recommended = false;
          $md->transformId = $t->id;
          $md->accentShift = $accentShift;
          $md->vowel = $accentedVowel;
          $md->save();
        }
      }
    }

    // Set the isLoc and recommended bits appropriately.
    // Do this separately as the loop above only includes modified forms.
    foreach ($nforms as $inflId => $tupleArray) {
      foreach ($tupleArray as $variant => $tuple) {
        $md = ModelDescription::get_by_modelId_inflectionId_variant_applOrder(
          $m->id, $inflId, $variant, 0);
        $md->isLoc = $tuple['isLoc'];
        $md->recommended = $tuple['recommended'];
        $md->save();
      }
    }

    // Regenerate the affected inflections for every lexem
    if (count($regenTransforms)) {
      $fileName = tempnam('/tmp', 'editModel_');
      $fp = fopen($fileName, 'w');
      foreach ($regenForms as $i => $regenRow) {
        $lm = $lexemModels[$i];
        foreach ($regenRow as $inflId => $formArray) {
          foreach ($formArray as $variant => $f) {
            $if = InflectedForm::create($f);
            fputcsv($fp, [$if->form, $if->formNoAccent, $if->formUtf8General, $lm->id, $inflId, $variant]);
          }
        }
      }
      foreach ($regenTransforms as $inflId => $ignored) {
        InflectedForm::deleteByModelNumberInflectionId($m->number, $inflId);
      }
      fclose($fp);
      chmod($fileName, 0666);
      db_executeFromOS("
        load data local infile \"{$fileName}\"
        into table InflectedForm
        fields terminated by \",\" optionally enclosed by \"\\\"\"
        (form, formNoAccent, formUtf8General, lexemModelId, inflectionId, variant)
      ");
      unlink($fileName);
    }

    // Propagate the recommended bit from ModelDescription to InflectedForm
    $q = sprintf("
      update InflectedForm i
      join LexemModel lm on i.lexemModelId = lm.id
      join Lexem l on lm.lexemId = l.id
      join ModelType mt on lm.modelType = mt.code
      join Model m on mt.canonical = m.modelType and lm.modelNumber = m.number
      join ModelDescription md on m.id = md.modelId and i.inflectionId = md.inflectionId and i.variant = md.variant
      set i.recommended = md.recommended
      where m.id = %s
      and md.applOrder = 0
    ", $m->id);
    db_execute($q);

    // Deal with changes in the model number
    if ($m->number != $nm->number) {
      if ($m->modelType == 'V') {
        $oldPm = ParticipleModel::loadByVerbModel($m->number);
        $oldPm->verbModel = $nm->number;
        $oldPm->save();
      } else if ($modelType == 'A') {
        // Update all participle models that use this adjective model
        $models = ParticipleModel::get_all_by_adjectiveModel($m->number);
        foreach ($models as $m) {
          $m->adjectiveModel = $nm->number;
          $m->save();
        }
      }

      foreach ($lexemModels as $lm) {
        $lm->modelNumber = $nm->number;
        $lm->save();
      }
    }

    if ($pm && ($pm->adjectiveModel != $npm->adjectiveModel)) {
      $npm->save();

      foreach ($participles as $p) { // $participles loaded before
        $p->save();
        $p->regenerateParadigm();
      }
    }

    $nm->save();
    util_redirect('../admin/index.php');
  }

  SmartyWrap::assign('om', $m);
  SmartyWrap::assign('opm', $pm);
  SmartyWrap::assign('oforms', $forms);
  SmartyWrap::assign('m', $nm);
  SmartyWrap::assign('pm', $npm);
  SmartyWrap::assign('forms', $nforms);
  SmartyWrap::assign('lexemModels', $lexemModels);
  SmartyWrap::assign('regenForms', $regenForms);
  SmartyWrap::assign('regenTransforms', $regenTransforms);
}

if ($m->modelType == 'V') {
  SmartyWrap::assign('adjModels', FlexModel::loadByType('A'));
}

SmartyWrap::assign('shortList', $shortList);
SmartyWrap::assign('inflectionMap', Inflection::mapById($inflections));
SmartyWrap::assign('wasPreviewed', $previewButton);
SmartyWrap::assign('errorMessage', $errorMessage);
SmartyWrap::assign('recentLinks', RecentLink::loadForUser());
SmartyWrap::addCss('paradigm', 'jqueryui');
SmartyWrap::addJs('jquery', 'jqueryui');
SmartyWrap::displayAdminPage('admin/editModel.tpl');

/****************************************************************************/

/**
 * $a, $b: arrays of ($form, $isLoc, $recommended) tuples. Only compares the forms.
 **/
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
 * Returns all LexemModels of model A$pm that belong to lexems having
 * the same form as participle InflectedForms of verbs of model VT$m.
 * Assumes that $pm is the correct participle (adjective) model for $m.
 **/
function loadParticiplesForVerbModel($model, $pm) {
  $infl = Inflection::loadParticiple();
  return Model::factory('LexemModel')
    ->table_alias('lmpart')
    ->join('Lexem', 'lmpart.lexemId = part.id', 'part')
    ->join('InflectedForm', 'part.formNoAccent = i.formNoAccent', 'i')
    ->join('LexemModel', 'i.lexemModelId = lminfin.id', 'lminfin')
    ->select('lmpart.*')
    ->where('lminfin.modelType', 'VT')
    ->where('lminfin.modelNumber', $model->number)
    ->where('i.inflectionId', $infl->id)
    ->where('lmpart.modelType', 'A')
    ->where('lmpart.modelNumber', $pm->adjectiveModel)
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
    $parts = preg_split('/_/', $name);
    if (StringUtil::startsWith($name, 'forms_')) {
      assert(count($parts) == 3);
      $inflId = $parts[1];
      $variant = $parts[2];
      $form = trim($value);
      if ($form) {
        $map[$inflId][$variant] = ['form' => $form, 'isLoc' => false, 'recommended' => false];
      }
    } else if (StringUtil::startsWith($name, 'isLoc_')) {
      assert(count($parts) == 3);
      $inflId = $parts[1];
      $variant = $parts[2];
      if (array_key_exists($variant, $map[$inflId])) {
        $map[$inflId][$variant]['isLoc'] = true;
      }
    } else if (StringUtil::startsWith($name, 'recommended_')) {
      assert(count($parts) == 3);
      $inflId = $parts[1];
      $variant = $parts[2];
      if (array_key_exists($variant, $map[$inflId])) {
        $map[$inflId][$variant]['recommended'] = true;
      }
    }
  }

  // Now reindex the array, in case the user left, for example, variant 1 empty but filled in variant 2.
  foreach ($map as $inflId => $variants) {
    $map[$inflId] = array_values($variants);
  }
}
