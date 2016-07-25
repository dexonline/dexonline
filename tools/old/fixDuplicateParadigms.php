<?php

require_once __DIR__ . '/../../phplib/util.php';

$lmIds = db_getArray('select lexemModelId from InflectedForm ' .
                     'group by lexemModelId, inflectionId, variant ' .
                     'having count(*) > 1 ');
$lmIds = array_values(array_unique($lmIds));

foreach ($lmIds as $i => $lmId) {
  $lm = LexemModel::get_by_id($lmId);
  $l = Lexem::get_by_id($lm->lexemId);
  printf("Fixing lexem %d of %d: [%s] (%d)\n",
         $i + 1, count($lmIds), $l->form, $l->id);
  $lm->regenerateParadigm();
}
