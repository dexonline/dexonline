<?php
require_once("../../phplib/Core.php"); 
User::mustHave(User::PRIV_EDIT);
Util::assertNotMirror();

define('SOURCE_ID', 25); // Dicționarul enciclopedic
$MODELS_TO_CAPITALIZE = ['I3', 'SP'];

$definitionId = Request::get('definitionId');
$jumpPrefix = Request::get('jumpPrefix', '');
$refreshButton = Request::has('refreshButton');
$saveButton = Request::has('saveButton');
$butPrev = Request::has('butPrev');
$butNext = Request::has('butNext');
$lexemIds = Request::get('lexemId');
$models = Request::get('model');
$capitalize = Request::has('capitalize');
$deleteOrphans = Request::has('deleteOrphans');

if ($definitionId && !$jumpPrefix) {
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
    Util::redirect($target);
  } else {
    FlashMessage::add('Ați ajuns la capătul listei de definiții.');
  }
}

// Load the database lexems
$dbl = Model::factory('Lexem')
  ->table_alias('l')
  ->select('l.*')
  ->join('EntryLexem', ['el.lexemId', '=', 'l.id'], 'el')
  ->join('EntryDefinition', ['ed.entryId', '=', 'el.entryId'], 'ed')
  ->where('ed.definitionId', $def->id)
  ->order_by_asc('formNoAccent')
  ->find_many();
$dblIds = Util::objectProperty($dbl, 'id');

$passedTests = false;

if ($saveButton) {
  // Dissociate all entries
  EntryDefinition::delete_all_by_definitionId($def->id);

  foreach ($lexemIds as $i => $lid) {
    if ($lid) {
      $m = $models[$i];

      // Create a new lexem or load the existing one
      if (StringUtil::startsWith($lid, '@')) {
        $lexem = Lexem::create(substr($lid, 1));

        // Create an entry
        $e = Entry::createAndSave($lexem->formNoAccent);

      } else {
        $lexem = Lexem::get_by_id($lid);
        $e = $lexem->getEntries()[0];
      }

      $needsCaps = prefixMatch($m, $MODELS_TO_CAPITALIZE);
      if ($capitalize && $needsCaps) {
        $lexem->setForm(AdminStringUtil::capitalize($lexem->form));
        $e->description = AdminStringUtil::capitalize($e->description);
        $e->save();
      }
        
      if ($m != "{$lexem->modelType}{$lexem->modelNumber}") {
        $model = Model::factory('ModelType')
               ->select('code')
               ->select('number')
               ->join('Model', ['canonical', '=', 'modelType'])
               ->where_raw("concat(code, number) = ? ", [$m])
               ->find_one();
        $lexem->modelType = $model->code;
        $lexem->modelNumber = $model->number;
      }

      $lexem->save();
      $lexem->regenerateParadigm();
      EntryLexem::associate($e->id, $lexem->id);

      // Associate the lexem with the definition
      EntryDefinition::associate($e->id, $def->id);
    }
  }

  // Delete orphaned lexems
  if ($deleteOrphans) {
    foreach ($dbl as $l) {
      $keepLexem = false;
      foreach ($l->getEntries() as $e) {
        $eds = EntryDefinition::get_all_by_entryId($e->id);
        if (count($eds)) {
          $keepLexem = true;
        } else {
          $e->delete();
        }
      }
      if (!$keepLexem) {
        $l->delete();
      }
    }
  }

  // Redirect back to the page
  $target = sprintf("?definitionId=%d&capitalize=%d&deleteOrphans=%d",
                    $def->id,
                    (int)$capitalize,
                    (int)$deleteOrphans);
  Util::redirect($target);

} else if ($refreshButton) {
  try {
    if (!count($lexemIds)) {
      throw new Exception('Trebuie să asociați cel puțin un lexem.');
    }

    foreach ($lexemIds as $i => $lid) {
      $m = $models[$i];

      if (empty($lid) xor empty($m)) {
        throw new Exception('Nu puteți avea un lexem fără modele nici invers.');
      }

      if ($lid) {
        if (StringUtil::startsWith($lid, '@')) {
          $lexem = Lexem::create(substr($lid, 1));
        } else {
          $lexem = Lexem::get_by_id($lid);
        }

        // Check that either the lexem is not in LOC or the model list is unchanged
        if ($lexem->isLoc && ($m != "{$lexem->modelType}{$lexem->modelNumber}")) {
          throw new Exception("Nu puteți schimba modelul unui lexem inclus în loc: {$lexem}.");
        }

        // Check that the lexem works with the model
        $model = Model::factory('ModelType')
               ->select('code')
               ->select('number')
               ->join('Model', ['canonical', '=', 'modelType'])
               ->where_raw("concat(code, number) = ? ", [$m])
               ->find_one();
        $l = Lexem::create($lexem->form, $model->code, $model->number);
        $ifs = $l->generateInflectedForms();
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
    $models[] = "{$l->modelType}{$l->modelNumber}";
  }

  SmartyWrap::assign('lexemIds', $dblIds);
  SmartyWrap::assign('models', $models);
}

SmartyWrap::assign('def', $def);
SmartyWrap::assign('capitalize', $capitalize);
SmartyWrap::assign('deleteOrphans', $deleteOrphans);
SmartyWrap::assign('passedTests', $passedTests);
SmartyWrap::addCss('admin');
SmartyWrap::addJs('select2Dev');
SmartyWrap::display('admin/deTool.tpl');

/*************************************************************************/

// Returns true iff any string in $prefixes is a prefix of $s
function prefixMatch($s, $prefixes) {
  foreach ($prefixes as $p) {
    if (StringUtil::startsWith($s, $p)) {
      return true;
    }
  }
  return false;
}
