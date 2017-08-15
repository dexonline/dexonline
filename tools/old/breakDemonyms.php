<?php
/**
 * Break demonyms into entries for M + MF ("prahovean") and F ("prahoveancă")
 **/

require_once __DIR__ . '/../phplib/Core.php';

Log::notice('started');

$entries = Model::factory('Entry')
         ->select('Entry.*')
         ->join('TreeEntry', ['Entry.id', '=', 'TreeEntry.entryId'])
         ->group_by('Entry.id')
         ->having_raw('count(*) > 1')
         ->order_by_asc('description')
         ->find_many();

foreach ($entries as $e) {
  $lexems = $e->getLexems();
  $trees = $e->getTrees();

  // skip entries with fewer than 2 trees or lexems
  if ((count($trees) < 2) || (count($lexems) < 2)) {
    continue;
  }

  // skip entries with lexem types other than M, MF, A, F
  // or where the feminine form is not the masculine form plus '-că'
  $masculine = false;
  $feminine = false;
  $badModelTypes = false;
  foreach ($lexems as $l) {
    if ($l->modelType == 'F') {
      $feminine = $l->formNoAccent;
    } else if (in_array($l->modelType, ['M', 'MF', 'A'])) {
      $masculine = $l->formNoAccent;
    } else {
      $badModelTypes = true;
    }
  }
  if (!$masculine ||
      !$feminine ||
      ($feminine != $masculine . 'că') ||
      $badModelTypes) {
    // Log::warning("Skipping [{$e}] with bad model types");
    continue;
  }

  // skip entries with variants ("arădan" / "arădean")
  $badForms = false;
  foreach ($lexems as $l) {
    if (($l->formNoAccent != $feminine) &&
        ($l->formNoAccent != $masculine)) {
      $badForms = true;
    }
  }
  if ($badForms) {
    continue;
  }

  // this entry can be broken
  $feme = $e->_clone(true, true, true, true);
  $feme->description = $feminine;
  $feme->save();
  Log::info("Cloned entry {$e->description} to {$feme->description}, new ID = {$feme->id}");

  foreach ($lexems as $l) {
    if ($l->formNoAccent == $feminine) {
      EntryLexem::dissociate($e->id, $l->id);
    } else {
      EntryLexem::dissociate($feme->id, $l->id);
    }
  }

  foreach ($trees as $t) {
    $desc = trim(explode('(', $t->description)[0]);
    if ($desc == $feminine) {
      TreeEntry::dissociate($t->id, $e->id);
    } else {
      TreeEntry::dissociate($t->id, $feme->id);
    }
  }

  $defs = Model::factory('Definition')
        ->table_alias('d')
        ->select('d.*')
        ->join('EntryDefinition', ['d.id', '=', 'ed.definitionId'], 'ed')
        ->where('ed.entryId', $e->id)
        ->order_by_asc('ed.id')
        ->find_many();
  foreach ($defs as $d) {
    if ($d->lexicon == $feminine) {
      EntryDefinition::dissociate($e->id, $d->id);
    } else {
      EntryDefinition::dissociate($feme->id, $d->id);
    }
  }
}

Log::notice('finished');
