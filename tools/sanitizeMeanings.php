<?php

/**
 * Sanitize all meanings
 **/

require_once __DIR__ . '/../phplib/Core.php';
require_once __DIR__ . '/../phplib/third-party/PHP-parsing-tool/Parser.php';
ini_set('memory_limit', '1024M');

$meanings = Model::factory('Meaning')
          ->order_by_asc('id')
          ->find_many();

foreach ($meanings as $i => $m) {
  $rep = Str::sanitize($m->internalRep);
  if ($rep != $m->internalRep) {
    Log::info("Modified\n[%s] into\n[%s]", $m->internalRep, $rep);
    $m->internalRep = $rep;
    $m->htmlRep = Str::htmlize($rep, 0);
    $m->save();
  }
  if ($i % 10000 == 0) {
    Log::info('***** %s of %s meanings processed.', $i, count($meanings));
  }
}
