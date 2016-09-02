<?php
require_once("../../phplib/util.php"); 
ini_set('memory_limit', '512M');
ini_set('max_execution_time', '3600');
util_assertModerator(PRIV_EDIT);
util_assertNotMirror();
DebugInfo::disable();

define('SHORT_LIST_LIMIT', 10);

$id = util_getRequestParameter('id');
$previewButton = util_getBoolean('previewButton');
$confirmButton = util_getBoolean('confirmButton');
$shortList = util_getBoolean('shortList');

$locPerm = util_isModerator(PRIV_LOC);

$m = FlexModel::get_by_id($id);
$pm = ParticipleModel::loadForModel($m);
$inflections = Model::factory('Inflection')
             ->where('modelType', $m->modelType)
             ->order_by_asc('rank')
             ->find_many();

// Generate the forms
$lexem = Lexem::create($m->exponent, $m->modelType, $m->number);
$ifs = $lexem->generateInflectedForms();

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

if (!$previewButton && !$confirmButton) {
  if (!$locPerm) {
    FlashMessage::add('Întrucât nu puteți modifica Lista Oficială de Cuvinte a ' .
                      'jocului de Scrabble, nu veți putea modifica unele dintre câmpuri.',
                      'warning');
  }

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
      FlashMessage::add("Modelul {$nm} există deja.");
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
          FlashMessage::add(sprintf('Nu pot extrage transformările între %s și %s.',
                                    $nm->exponent,
                                    htmlentities($tuple['form'])));
        }
      }
    }
  }
  
  // Load the affected lexems. For each lexem, inflection and transform
  // list, generate a new form.
  $limit = ($shortList && !$confirmButton) ? SHORT_LIST_LIMIT : 0;
  $lexems = Lexem::loadByCanonicalModel($m->modelType, $m->number, $limit);
  $regenForms = [];
  $errorCount = 0; // Do not report thousands of similar errors.
  foreach ($lexems as $l) {
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
        if (!$result && ($errorCount < 3)) {
          FlashMessage::add(sprintf('Nu pot calcula una din formele lexemului %s.',
                                    htmlentities($l->form)));
          $errorCount++;
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
        FlashMessage::add(sprintf('Nu pot declina participiul "%s" conform modelului A%s.',
                                  htmlentities($p->form), $npm->adjectiveModel));
      }
    }

    SmartyWrap::assign('participles', $participles);
  }

  if ($confirmButton) {
    Log::notice("Saving model {$m->id} ({$m}), this could take a while");
    
    // Save the transforms and model descriptions
    Log::debug('Saving transforms and model descriptions');
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
    Log::debug('Saving isLoc and recommended bits');
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
    Log::debug('Regenerating modified inflections');
    if (count($regenTransforms)) {
      $fileName = tempnam('/tmp', 'editModel_');
      $fp = fopen($fileName, 'w');
      foreach ($regenForms as $i => $regenRow) {
        $l = $lexems[$i];
        foreach ($regenRow as $inflId => $formArray) {
          foreach ($formArray as $variant => $f) {
            if (ConstraintMap::allows($l->restriction, $inflId, $variant)) {
              $if = InflectedForm::create($f);
              fputcsv($fp, [$if->form, $if->formNoAccent, $if->formUtf8General, $l->id, $inflId, $variant]);
            }
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
        (form, formNoAccent, formUtf8General, lexemId, inflectionId, variant)
      ");
      unlink($fileName);
    }

    // Propagate the recommended bit from ModelDescription to InflectedForm
    Log::debug('Propagating the "recommended" bit from ModelDescriptions to InflectedForms');
    $q = sprintf("
      update InflectedForm i
      join Lexem l on i.lexemId = l.id
      join ModelType mt on l.modelType = mt.code
      join Model m on mt.canonical = m.modelType and l.modelNumber = m.number
      join ModelDescription md on m.id = md.modelId and i.inflectionId = md.inflectionId and i.variant = md.variant
      set i.recommended = md.recommended
      where m.id = %s
      and md.applOrder = 0
    ", $m->id);
    db_execute($q);

    // Deal with changes in the model number
    if ($m->number != $nm->number) {
      Log::debug('Propagating model number change to dependent models');
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

      foreach ($lexems as $l) {
        $l->modelNumber = $nm->number;
        $l->save();
      }
    }

    if ($pm && ($pm->adjectiveModel != $npm->adjectiveModel)) {
      Log::debug('Regenerating participle lexems');
      $npm->save();

      foreach ($participles as $p) { // $participles loaded before
        $p->save();
        $p->regenerateParadigm();
      }
    }

    $nm->save();
    Log::notice("Saving model {$nm->id} ({$nm}) done");
    util_redirect('../admin/index.php');
  } else { // preview button
    if (!FlashMessage::hasErrors()) {
      FlashMessage::add('Examinați modificările și, dacă totul arată normal, apăsați ' .
                        'butonul „Salvează”. Dacă nu, continuați editarea și apăsați ' .
                        'din nou butonul „Testează”.', 'info');

    }
  }

  SmartyWrap::assign('m', $nm);
  SmartyWrap::assign('pm', $npm);
  SmartyWrap::assign('forms', $nforms);
  SmartyWrap::assign('lexems', $lexems);
  SmartyWrap::assign('regenForms', $regenForms);
  SmartyWrap::assign('regenTransforms', $regenTransforms);
}

if ($m->modelType == 'V') {
  SmartyWrap::assign('adjModels', FlexModel::loadByType('A'));
}

SmartyWrap::assign('shortList', $shortList);
SmartyWrap::assign('inflectionMap', Inflection::mapById($inflections));
SmartyWrap::assign('previewPassed', $previewButton && !FlashMessage::hasErrors());
SmartyWrap::assign('locPerm', $locPerm);
SmartyWrap::assign('recentLinks', RecentLink::loadForUser());
SmartyWrap::addCss('paradigm', 'admin');
SmartyWrap::display('admin/editModel.tpl');

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
 * Returns all lexems of model A$pm that have the same form as participle
 * InflectedForms of verbs of model VT$model.
 * Assumes that $pm is the correct participle (adjective) model for $model.
 **/
function loadParticiplesForVerbModel($model, $pm) {
  $infl = Inflection::loadParticiple();
  return Model::factory('Lexem')
    ->table_alias('part')
    ->select('part.*')
    ->join('InflectedForm', 'part.formNoAccent = i.formNoAccent', 'i')
    ->join('Lexem', 'i.lexemId = infin.id', 'infin')
    ->where('infin.modelType', 'VT')
    ->where('infin.modelNumber', $model->number)
    ->where('i.inflectionId', $infl->id)
    ->where('part.modelType', 'A')
    ->where('part.modelNumber', $pm->adjectiveModel)
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
