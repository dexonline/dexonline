<?php
require_once("../../phplib/util.php"); 
util_assertModerator(PRIV_EDIT);
util_assertNotMirror();

define('SOURCE_ID', 25); // Dicționarul enciclopedic
$MODELS_TO_CAPITALIZE = ['I3', 'SP'];

$definitionId = util_getRequestParameter('definitionId');
$jumpPrefix = util_getRequestParameterWithDefault('jumpPrefix', '');
$butTest = util_getRequestParameter('butTest');
$butSave = util_getRequestParameter('butSave');
$butPrev = util_getRequestParameter('butPrev');
$butNext = util_getRequestParameter('butNext');
$lexemIds = util_getRequestParameter('lexemId');
$capitalize = util_getBoolean('capitalize');
$deleteOrphans = util_getBoolean('deleteOrphans');

// We need to save model info as JSON because it is 2-dimensional
// (a list of lists of models) and PHP cannot parse the form data correctly.
$jsonModels = util_getRequestParameter('jsonModels');
$models = json_decode($jsonModels);

if ($definitionId) {
  $def = Definition::get_by_id($definitionId);
} else {
  // Load the first definition after $jumpPrefix from DE 
  $def = Model::factory('Definition')
       ->where('sourceId', SOURCE_ID)
       ->where('status', Definition::ST_ACTIVE)
       ->where_gte('lexicon', $jumpPrefix)
       ->order_by_asc('lexicon')
       ->order_by_asc('id')
       ->find_one();
}

if (!$def) {
  die("Definiția cerută nu există.");
}

if ($butPrev || $butNext) {
  // Load the prev/next definition
  if ($butPrev) {
    $other = Model::factory('Definition')
           ->where('sourceId', SOURCE_ID)
           ->where('status', Definition::ST_ACTIVE)
           ->where_raw('((lexicon < ?) or (lexicon = ? and id < ?))',
                       [$def->lexicon, $def->lexicon, $def->id])
           ->order_by_desc('lexicon')
           ->order_by_desc('id')
           ->find_one();    
  } else {
    $other = Model::factory('Definition')
           ->where('sourceId', SOURCE_ID)
           ->where('status', Definition::ST_ACTIVE)
           ->where_raw('((lexicon > ?) or (lexicon = ? and id > ?))',
                       [$def->lexicon, $def->lexicon, $def->id])
           ->order_by_asc('lexicon')
           ->order_by_asc('id')
           ->find_one();
  }
  if ($other) {
    // Redirect to the page
    $target = sprintf("?definitionId=%d&capitalize=%d&deleteOrphans=%d",
                      $other->id,
                      (int)$capitalize,
                      (int)$deleteOrphans);
    util_redirect($target);
  } else {
    FlashMessage::add('Ați ajuns la capătul listei de definiții.');
  }
}

// Load the database lexems
$dbl = Model::factory('Lexem')
     ->select('Lexem.*')
     ->join('LexemDefinitionMap', 'Lexem.id = lexemId', 'ldm')
     ->where('ldm.definitionId', $def->id)
     ->order_by_asc('formNoAccent')
     ->find_many();
$dblIds = util_objectProperty($dbl, 'id');

$passedTests = false;

if ($butSave) {
  // Dissociate all lexems
  LexemDefinitionMap::deleteByDefinitionId($def->id);

  foreach ($lexemIds as $i => $lid) {
    if ($lid) {
      // Create a new lexem or load the existing one
      if (StringUtil::startsWith($lid, '@')) {
        $lexem = Lexem::create(substr($lid, 1));
        $lexem->save();
      } else {
        $lexem = Lexem::get_by_id($lid);
      }

      // Associate the lexem with the definition
      LexemDefinitionMap::associate($lexem->id, $def->id);

      // There's bit of complexity here because deTool.php doesn't support
      // restrictions, but the existing lexem models may have them.
      // So we confront the new and old array of lexem models, copy any
      // common ones and delete the ones that are gone.

      // Load existing lexem models
      $dbLms = $lexem->getLexemModels();

      // Create the new set of lexem models
      $lms = [];
      $needsCaps = false;
      foreach ($models[$i] as $m) {
        $model = Model::factory('ModelType')
               ->select('code')
               ->select('number')
               ->join('Model', ['canonical', '=', 'modelType'])
               ->where_raw("concat(code, number) = ? ", [$m])
               ->find_one();
        $lm = LexemModel::create($model->code, $model->number);
        $lm->setLexem($lexem);
        $lm->lexemId = $lexem->id;
        $lm->displayOrder = 1 + count($lms);
        $oldLm = lmSearch($dbLms, $model->code, $model->number);
        if ($oldLm) {
          $lm->restriction = $oldLm->restriction;
          $lm->tags = $oldLm->tags;
          $lm->isLoc = $oldLm->isLoc;
        }
        $lms[] = $lm;
        $needsCaps |= prefixMatch($m, $MODELS_TO_CAPITALIZE);
      }
      $lexem->setLexemModels($lms);

      // Delete old lexem models
      foreach ($dbLms as $lm) {
        $lm->delete();
      }

      if ($needsCaps) {
        $lexem->setForm(AdminStringUtil::capitalize($lexem->form));
      }
      
      $lexem->deepSave();
    }
  }

  // Delete orphaned lexems
  if ($deleteOrphans) {
    foreach ($dbl as $l) {
      $ldms = LexemDefinitionMap::get_all_by_lexemId($l->id);
      if (!count($ldms)) {
        $l->delete();
      }
    }
  }

  // Redirect back to the page
  $target = sprintf("?definitionId=%d&capitalize=%d&deleteOrphans=%d",
                    $def->id,
                    (int)$capitalize,
                    (int)$deleteOrphans);
  util_redirect($target);

} else if ($butTest) {
  try {
    if (!count($lexemIds)) {
      throw new Exception('Trebuie să asociați cel puțin un lexem.');
    }

    foreach ($lexemIds as $i => $lid) {
      if (empty($lid) xor empty($models[$i])) {
        throw new Exception('Nu puteți avea un lexem fără modele nici invers.');
      }

      if ($lid) {
        if (StringUtil::startsWith($lid, '@')) {
          $lexem = Lexem::create(substr($lid, 1));
        } else {
          $lexem = Lexem::get_by_id($lid);
        }

        // Check that either the lexem is not in LOC or the model list is unchanged
        if ($lexem->isLoc() && !sameModels($models[$i], $lexem->getLexemModels())) {
          throw new Exception("Nu puteți schimba modelele unui lexem inclus în loc: {$lexem}.");
        }

        // Check that the lexem works with every model
        foreach ($models[$i] as $m) {
          $model = Model::factory('ModelType')
                 ->select('code')
                 ->select('number')
                 ->join('Model', ['canonical', '=', 'modelType'])
                 ->where_raw("concat(code, number) = ? ", [$m])
                 ->find_one();
          $lm = LexemModel::create($model->code, $model->number);
          $lm->setLexem($lexem);
          $ifs = $lm->generateInflectedForms();
          if (!is_array($ifs)) {
            $infl = Inflection::get_by_id($ifs);
            $msg = "Lexemul „%s” nu poate fi flexionat conform modelului %s.";
            throw new Exception(sprintf($msg, $lexem->form, $m));
          }
        }
      }
    }
    $passedTests = true;
  } catch (Exception $e) {
    FlashMessage::add($e->getMessage());
  }
  SmartyWrap::assign('lexemIds', $lexemIds);
  SmartyWrap::assign('models', $models);
} else {
  $models = [];
  foreach ($dbl as $l) {
    $m = [];
    foreach($l->getLexemModels() as $lm) {
      $m[] = "{$lm->modelType}{$lm->modelNumber}";
    }
    $models[] = $m;
  }

  SmartyWrap::assign('lexemIds', $dblIds);
  SmartyWrap::assign('models', $models);
}

SmartyWrap::assign('def', $def);
SmartyWrap::assign('capitalize', $capitalize);
SmartyWrap::assign('deleteOrphans', $deleteOrphans);
SmartyWrap::assign('passedTests', $passedTests);
SmartyWrap::addCss('jqueryui', 'select2');
SmartyWrap::addJs('jquery', 'jqueryui', 'select2', 'select2Dev');
SmartyWrap::displayAdminPage('admin/deTool.tpl');

/*************************************************************************/

// Searches for a LexemModel in an array, ignoring the restriction field */
function lmSearch($lms, $type, $number) {
  foreach ($lms as $lm) {
    if (($lm->modelType == $type) && ($lm->modelNumber == $number)) {
      return $lm;
    }
  }
  return null;
}

// Returns true iff any string in $prefixes is a prefix of $s
function prefixMatch($s, $prefixes) {
  foreach ($prefixes as $p) {
    if (StringUtil::startsWith($s, $p)) {
      return true;
    }
  }
  return false;
}

// $models: comma-separated list of models
// $lms: old lexem models
function sameModels($models, $lms) {
  if (count($models) != count($lms)) {
    return false;
  }
  foreach ($lms as $i => $lm) {
    if ($models[$i] != "{$lm->modelType}{$lm->modelNumber}") {
      return false;
    }
  }
  return true;
}
