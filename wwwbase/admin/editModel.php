<?php
require_once("../../phplib/Core.php");
ini_set('memory_limit', '512M');
ini_set('max_execution_time', '3600');
User::mustHave(User::PRIV_EDIT);
Util::assertNotMirror();
DebugInfo::disable();

$id = Request::get('id');
$saveButton = Request::has('saveButton');

$m = FlexModel::get_by_id($id);
$pm = ParticipleModel::loadForModel($m);
$inflections = Model::factory('Inflection')
  ->where('modelType', $m->modelType)
  ->order_by_asc('rank')
  ->find_many();

if ($saveButton) {
  Log::notice("Reading form data for model {$m->id} ({$m})");
  $orig = FlexModel::get_by_id($id); // original, for comparison
  $m->number = Request::get('number');
  $m->description = Request::get('description');
  $m->exponent = Request::get('exponent');

  $origPm = ParticipleModel::loadForModel($orig);
  if ($pm) {
    $pm->adjectiveModel = Request::get('participleNumber');
  }

  Log::notice('Validating model number');
  if (($m->number != $orig->number) &&
      FlexModel::get_by_modelType_number($m->modelType, $m->number)) {
    FlashMessage::add("Modelul {$m} există deja.", 'danger');
  }

  Log::notice('Extracting transforms');
  $forms = readRequest($inflections);
  extractTransforms($m, $forms, false);

  if (!FlashMessage::hasErrors()) {
    Log::notice('Saving transforms and model descriptions');
    extractTransforms($m, $forms, true);

    Log::notice('Updating participle models');
    if ($pm && ($pm->adjectiveModel != $origPm->adjectiveModel)) {
      $pm->save();

      $participles = loadParticiplesForVerbModel($orig, $origPm);
      foreach ($participles as $p) {
        $p->modelNumber = $pm->adjectiveModel;
        $p->save();
      }
    }

    if ($orig->number != $m->number) {
      if ($orig->modelType == 'V') {
        Log::notice('Propagating new verb number to ParticipleModel');
        $oldPm = ParticipleModel::loadByVerbModel($orig->number);
        $oldPm->verbModel = $m->number;
        $oldPm->save();
      } else if ($m->modelType == 'A') {
        Log::notice('Replacing adjective number %d with %d in ParticipleModels',
                    $orig->number, $m->number);
        $partModels = ParticipleModel::get_all_by_adjectiveModel($orig->number);
        foreach ($partModels as $partModel) {
          $partModel->adjectiveModel = $m->number;
          $partModel->save();
        }
      }

      Log::notice('Propagating model number change to lexemes');
      $query = sprintf(
        'update Lexeme l ' .
        'join ModelType mt on l.modelType = mt.code ' .
        'set l.modelNumber = "%s" ' .
        'where mt.canonical = "%s" ' .
        'and l.modelNumber = "%s" ',
        addslashes($m->number), $m->modelType, addslashes($orig->number));
      DB::execute($query);
    }

    $m->save();
    Log::notice("Saving model {$m->id} ({$m}) done");
    Variable::poke('Count.obsoleteParadigms', Lexeme::countObsoleteParadigms());
    FlashMessage::add('Am salvat modificările.', 'success');
    Util::redirect("editModel.php?id={$m->id}");
  }
} else {

  // first time loading the page
  RecentLink::add("Editare model: {$m}");

  // Generate the exponent's forms
  $lexeme = Lexeme::create($m->exponent, $m->modelType, $m->number);
  $lexeme->setAnimate(true);
  $ifs = $lexeme->generateInflectedForms();

  // Map the forms by inflectionId and variant. Include inflectionId's that
  // the current model doesn't have
  $forms = [];
  foreach ($inflections as $infl) {
    $forms[$infl->id] = [];
  }

  $mds = ModelDescription::loadForModel($m->id);
  foreach ($ifs as $i => $if) {
    // This works because $ifs and $mds are both ordered by inflectionId,
    // then by variant
    $forms[$if->inflectionId][] = [
      'form' => $if->form,
      'recommended' => $mds[$i]->recommended,
      'apocope' => $mds[$i]->apocope,
    ];
  }
}

SmartyWrap::assign([
  'm' => $m,
  'pm' => $pm,
  'forms' => $forms,
  'inflectionMap' => Util::mapById($inflections),
]);

if ($m->modelType == 'V') {
  SmartyWrap::assign('adjModels', FlexModel::loadByType('A'));
}

SmartyWrap::addCss('paradigm', 'admin');
SmartyWrap::display('admin/editModel.tpl');

/****************************************************************************/

/**
 * Returns all lexemes of model A$pm that have the same form as participle
 * InflectedForms of verbs of model VT$model.
 * Assumes that $pm is the correct participle (adjective) model for $model.
 **/
function loadParticiplesForVerbModel($model, $pm) {
  $infl = Inflection::loadParticiple();
  return Model::factory('Lexeme')
    ->table_alias('part')
    ->select('part.*')
    ->join('InflectedForm', 'part.formNoAccent = i.formNoAccent', 'i')
    ->join('Lexeme', 'i.lexemeId = infin.id', 'infin')
    ->where('infin.modelType', 'VT')
    ->where('infin.modelNumber', $model->number)
    ->where('i.inflectionId', $infl->id)
    ->where('part.modelType', 'A')
    ->where('part.modelNumber', $pm->adjectiveModel)
    ->order_by_asc('part.formNoAccent')
    ->find_many();
}

/**
 * Read forms, recommended and apocope checkboxes from the request.
 * The map is already populated with all the applicable inflection IDs.
 * InflectionId's and variants are coded in the request parameters.
 **/
function readRequest($inflections) {
  $map = [];
  foreach ($inflections as $infl) {
    $map[$infl->id] = [];
  }

  foreach ($_REQUEST as $name => $value) {
    $parts = preg_split('/_/', $name);
    if (in_array($parts[0], ['forms', 'recommended', 'apocope'])) {
      assert(count($parts) == 3);
      $inflId = $parts[1];
      $variant = $parts[2];

      if ($parts[0] == 'forms') {
        $form = trim($value);
        if ($form) {
          $map[$inflId][$variant] = [
            'form' => $form,
            'recommended' => false,
            'apocope' => false,
          ];
        }
      } else if ($parts[0] == 'recommended') {
        if (array_key_exists($variant, $map[$inflId])) {
          $map[$inflId][$variant]['recommended'] = true;
        }
      } else { // $parts[0] == 'apocope'
        if (array_key_exists($variant, $map[$inflId])) {
          $map[$inflId][$variant]['apocope'] = true;
        }
      }
    }
  }

  // Now reindex the array, in case the user left, for example, variant 1
  // empty but filled in variant 2.
  foreach ($map as $inflId => $variants) {
    $map[$inflId] = array_values($variants);
  }

  return $map;
}

// $m: model being edited
// $forms: map of inflection ID to array of tuples (form, recommended, apocope)
// When $save = true, saves ModelDescriptions.
// When $save = false, just sets FlashMessages on errors.
function extractTransforms($m, $forms, $save) {
  Log::notice('Extracting transforms (save=%d)', $save);

  if ($save) {
    ModelDescription::delete_all_by_modelId($m->id);
  }

  $isPronoun = ($m->modelType == 'P');
  foreach ($forms as $inflId => $variants) {
    foreach ($variants as $variant => $tuple) {
      $transforms = FlexStr::extractTransforms($m->exponent, $tuple['form'], $isPronoun);
      if ($transforms === null) {
        FlashMessage::add(sprintf('Nu pot extrage transformările între %s și %s.',
                                  $m->exponent,
                                  htmlentities($tuple['form'])),
                          'danger');
      } else if ($save) {

        $accentShift = array_pop($transforms);
        if ($accentShift != ModelDescription::UNKNOWN_ACCENT_SHIFT &&
            $accentShift != ModelDescription::NO_ACCENT_SHIFT) {
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
          $md->recommended = $tuple['recommended'];
          $md->apocope = $tuple['apocope'];
          $md->transformId = $t->id;
          $md->accentShift = $accentShift;
          $md->vowel = $accentedVowel;
          $md->save();
        }
      }
    }
  }
}
