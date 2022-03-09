<?php

/**
 * Structure definitions from a given source.
 **/

require_once __DIR__ . '/../lib/Core.php';

function main() {
  $ocrs = Model::factory('OCR')
    ->where('status', 'raw')
    ->where('sourceId', 88)
    ->where_not_in('id', [391440])
    ->find_many();

  foreach ($ocrs as $ocr) {
    $d = Model::factory('Definition')->create();
    $d->status = Definition::ST_ACTIVE;
    $d->userId = 1;
    $d->sourceId = $ocr->sourceId;
    $d->structured = 0;
    $d->internalRep = $ocr->ocrText;
    $d->process();
    $d->save();
    // do not set the volume and page here as the lexicon could be completely wrong

    $ocr->definitionId = $d->id;
    $ocr->editorId = 1;
    $ocr->status = 'published';
    $ocr->save();
    print "OCR {$ocr->id} -> def {$d->id}\n";
  }
}

main();
