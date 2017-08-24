<?php
/**
 * Break verbs into entries for the verb, the long infinitive and the participle.
 **/

require_once __DIR__ . '/../phplib/Core.php';

Log::notice('started');

$entries = Model::factory('Entry')
         ->select('Entry.*')
         ->join('EntryLexem', ['Entry.id', '=', 'EntryLexem.entryId'])
         ->group_by('Entry.id')
         ->having_raw('count(*) > 1')
         ->order_by_asc('description')
         ->find_many();

$longInfInflection = Inflection::loadLongInfinitive();
$partInflection = Inflection::loadParticiple();

foreach ($entries as $e) {
  $lexems = $e->getLexems();
  $trees = $e->getTrees();

  // skip entries with lexem types other than V, VT, F, A
  // skip entries with no verbs
  // skip entries with verbs only ("zuzui"), which presumably were already split
  $badModelTypes = false;
  $anyVerbs = false;
  $onlyVerbs = true;
  foreach ($lexems as $l) {
    if (in_array($l->modelType, ['V', 'VT'])) {
      $anyVerbs = true;
    } else if (in_array($l->modelType, ['F', 'A'])) {
      $onlyVerbs = false;
    } else {
      $badModelTypes = true;
    }
  }
  if (!$anyVerbs || $onlyVerbs || $badModelTypes) {
    continue;
  }

  // collect allowed forms
  $allowedLongInfForms = [];
  $allowedPartForms = [];

  foreach ($lexems as $l) {
    if (in_array($l->modelType, ['V', 'VT'])) {
      $ifs = InflectedForm::get_all_by_lexemId_inflectionId($l->id, $longInfInflection->id);
      foreach ($ifs as $if) {
        $allowedLongInfForms[] = $if->formNoAccent;
      }
    }
    if ($l->modelType == 'VT') {
      $ifs = InflectedForm::get_all_by_lexemId_inflectionId($l->id, $partInflection->id);
      foreach ($ifs as $if) {
        $allowedPartForms[] = $if->formNoAccent;
      }
    }
  }

  $verbLexemes = [];
  $longInfLexemes = [];
  $partLexemes = [];

  // bail if any lexemes fall outside the allowed forms
  $badForms = false;
  foreach ($lexems as $l) {
    switch ($l->modelType) {
    case 'V':
    case 'VT':
      $verbLexemes[] = $l;
      break;

    case 'F':
      if (in_array($l->formNoAccent, $allowedLongInfForms)) {
        $longInfLexemes[] = $l;
      } else {
        $badForms = true;
        // Log::warning("Skipping [{$e}] due to bad lexeme {$l}]");
      }
      break;

    case 'A':
      if (in_array($l->formNoAccent, $allowedPartForms)) {
        $partLexemes[] = $l;
      } else {
        $badForms = true;
        // Log::warning("Skipping [{$e}] due to bad lexeme {$l}]");
      }
      break;
    }
  }
  if ($badForms) {
    continue;
  }

  Log::info("Splitting [%s] with %d trees", $e, count($trees));

  // make sure the entry is named after the infinitive
  $shortDesc = trim(explode('(', $e->description)[0]);
  if (in_array($shortDesc, $allowedLongInfForms) ||
      in_array($shortDesc, $allowedPartForms)) {
    Log::warning("Renaming [%s] to [%s]", $e, $verbLexemes[0]->formNoAccent);
    $e->description = $verbLexemes[0]->formNoAccent;
    $e->save();
  }

  // delete empty trees (keep at least one)
  // delete them in reverse order so that the tree for the infinitive is never deleted even if empty
  $numTrees = count($trees);
  foreach (array_reverse($trees) as $t) {
    if ($t->canDelete() && ($numTrees > 1)) {
      Log::warning("Deleting empty tree {$t->description}");
      $t->delete();
      $numTrees--;
    }
  }
  if ($numTrees != count($trees)) {
    $trees = $e->getTrees(true);
    Log::warning("%d trees left", count($trees));
  }

  $allowedInfForms = Util::objectProperty($verbLexemes, 'formNoAccent');

  $defs = $e->getDefinitions();

  // create a long infinitive entry and move lexemes, trees and definitions over
  $longInfE = null;
  if (count($longInfLexemes)) {
    $longInfE = $e->_clone(false, false, false, true);
    $longInfE->description = $longInfLexemes[0]->formNoAccent;
    $longInfE->save();
    $anyLongInfTrees = false;
    $anyLongInfDefs = false;
    foreach ($longInfLexemes as $l) {
      EntryLexem::dissociate($e->id, $l->id);
      EntryLexem::associate($longInfE->id, $l->id);
    }
    foreach ($trees as $t) {
      $desc = trim(explode('(', $t->description)[0]);
      if (in_array($desc, $allowedLongInfForms)) {
        TreeEntry::dissociate($t->id, $e->id);
        TreeEntry::associate($t->id, $longInfE->id);
        $anyLongInfTrees = true;
      }
    }
    foreach ($defs as $d) {
      if (in_array($d->lexicon, $allowedLongInfForms)) {
        EntryDefinition::dissociate($e->id, $d->id);
        EntryDefinition::associate($longInfE->id, $d->id);
        $anyLongInfDefs = true;
      }
    }
  }

  // create a participle entry and move lexemes, trees and definitions over
  $partE = null;
  if (count($partLexemes)) {
    $partE = $e->_clone(false, false, false, true);
    $partE->description = $partLexemes[0]->formNoAccent;
    $partE->save();
    $anyPartTrees = false;
    $anyPartDefs = false;
    foreach ($partLexemes as $l) {
      EntryLexem::dissociate($e->id, $l->id);
      EntryLexem::associate($partE->id, $l->id);
    }
    foreach ($trees as $t) {
      $desc = trim(explode('(', $t->description)[0]);
      if (in_array($desc, $allowedPartForms)) {
        TreeEntry::dissociate($t->id, $e->id);
        TreeEntry::associate($t->id, $partE->id);
        $anyPartTrees = true;
      }
    }
    foreach ($defs as $d) {
      if (in_array($d->lexicon, $allowedPartForms)) {
        EntryDefinition::dissociate($e->id, $d->id);
        EntryDefinition::associate($partE->id, $d->id);
        $anyPartDefs = true;
      }
    }
  }

  // if the new entries have no trees or definitions of their own, use the infinitive ones
  $trees = $e->getTrees(true);
  $defs = $e->getDefinitions(true);

  if ($longInfE && !$anyLongInfTrees) {
    foreach ($trees as $t) {
      TreeEntry::associate($t->id, $longInfE->id);
    }
  }
  if ($longInfE && !$anyLongInfDefs) {
    foreach ($defs as $d) {
      EntryDefinition::associate($longInfE->id, $d->id);
    }
  }
  
  if ($partE && !$anyPartTrees) {
    foreach ($trees as $t) {
      TreeEntry::associate($t->id, $partE->id);
    }
  }
  if ($partE && !$anyPartDefs) {
    foreach ($defs as $d) {
      EntryDefinition::associate($partE->id, $d->id);
    }
  }
}

Log::notice('finished');
