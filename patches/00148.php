<?php

/**
 * Second pass: process lexems exclusive to Onomastic and DE.
 **/

$ONOMASTIC_ID = 57;
$DE_ID = 25;
$SAINEANU_ID = 42;

$lexems = Model::factory('Lexem')
        ->select('l.*')
        ->table_alias('l')
        ->join('LexemDefinitionMap', ['l.id', '=', 'ldm.lexemId'], 'ldm')
        ->join('Definition', ['ldm.definitionId', '=', 'd.id'], 'd')
        ->join('LexemModel', ['lm.lexemId', '=', 'l.id'], 'lm')
        ->where('d.sourceId', $ONOMASTIC_ID)
        ->where('lm.modelType', 'T')
        ->order_by_asc('l.form')
        ->find_many();
$n = count($lexems);
$i = 0;

foreach ($lexems as $l) {
  $i++;
  print "*** {$i}/{$n}: {$l->form}\n";

  $otherDefs = Model::factory('Definition')
             ->table_alias('d')
             ->join('LexemDefinitionMap', ['d.id', '=', 'ldm.definitionId'], 'ldm')
             ->where_not_in('d.sourceId', [$ONOMASTIC_ID, $DE_ID, $SAINEANU_ID])
             ->where('ldm.lexemId', $l->id)
             ->count();

  if ($otherDefs) {
    print "Skipping because it has {$otherDefs} definitions outside Onomastic, DE and Șăineanu.\n";
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
        print "Form is now {$l->form}.\n";
        $changes = true;
      }
    }

    // Replace T1 models with I3 models
    if ((count($lms) == 0) ||
        ((count($lms) == 1) && ($lms[0]->modelType == 'T'))) {
      print "Model is now I3.\n";
      $changes = true;
    }

    if ($changes) {
      change($l, $lms);
    }
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
