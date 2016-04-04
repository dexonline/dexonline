<?php

/**
 * Capitalize lexems from the Onomastic dictionary and replace
 * the T1 model with I3. */

$ONOMASTIC_ID = 57;

$defs = Definition::get_all_by_sourceId($ONOMASTIC_ID);
$n = count($defs);
$i = 0;

foreach ($defs as $d) {
  $lexems = Model::factory('Lexem')
          ->select('l.*')
          ->table_alias('l')
          ->join('LexemDefinitionMap', ['l.id', '=', 'ldm.lexemId'], 'ldm')
          ->where('ldm.definitionId', $d->id)
          ->find_many();

  foreach ($lexems as $l) {
    $otherDefs = Model::factory('Definition')
               ->table_alias('d')
               ->join('LexemDefinitionMap', ['d.id', '=', 'ldm.definitionId'], 'ldm')
               ->where_not_equal('d.sourceId', $ONOMASTIC_ID)
               ->where('ldm.lexemId', $l->id)
               ->count();

    if ($otherDefs) {
      print "Skipping {$l->form} because it has {$otherDefs} definitions from other sources.\n";
    } else {
      $lms = $l->getLexemModels();

      $changes = false;
      // Capitalize lexems for the I3 and T1 models
      if ((count($lms) <= 1) &&
          (strpos($l->form, "'") === false) &&
          ((count($lms) == 0) ||
           ($lms[0]->modelType == 'T') ||
           (($lms[0]->modelType == 'I') && ($lms[0]->modelNumber == '3')))) {
        $form = mb_convert_case($l->form, MB_CASE_TITLE);
        if ($form != $l->form) {
          $l->form = $form;
          $l->formNoAccent = str_replace("'", '', $l->form);
          $changes = true;
        }
      }

      // Replace T1 models with I3 models
      if ((count($lms) == 0) ||
          ((count($lms) == 1) && ($lms[0]->modelType == 'T'))) {
        $changes = true;
      }

      if ($changes) {
        change($l, $lms);
      }
    }
  }
  
  $i++;
  if ($i % 100 == 0) {
    print "Processed {$i}/{$n} definitions.\n";
  }

}

/**************************************************************************/

function change(&$l, $lms) {
  $orig = Lexem::get_by_id($l->id);

  printf("Migrating %s, %d models:", $orig->form, count($lms));
  foreach ($lms as $lm) {
    print " {$lm}";
  }
  print "\n";

  // delete old LexemModels
  foreach ($lms as $lm) {
    $lm->delete();
  }

  // create LexemModels
  $lm = Model::factory('LexemModel')->create();
  $lm->lexemId = $l->id;
  $lm->setLexem($l); // Otherwise it will reload the original
  $lm->displayOrder = 1;
  $lm->modelType = 'I';
  $lm->modelNumber = '3';
  $lm->restriction = '';
  $lm->tags = '';
  $lm->isLoc = 0;
  $lm->generateInflectedFormMap();

  $l->setLexemModels([$lm]);

  $l->deepSave();
}

?>
