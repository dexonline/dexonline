<?php

/**
 * Converts a sample of definitions to HTML and outputs a speed report.
 **/

require_once __DIR__ . '/../phplib/Core.php';

define('SAMPLE_SIZE', 100000);
define('BATCH_SIZE', 10000);

DebugInfo::resetClock();

$sumLength = 0;
$sumMillis = 0;
$remaining = SAMPLE_SIZE;

do {
  $limit = min($remaining, BATCH_SIZE);
  $defs = Model::factory('Definition')
        ->where_in('status', [Definition::ST_ACTIVE, Definition::ST_HIDDEN])
        ->order_by_expr('rand()')
        ->limit($limit)
        ->find_many();

  $millis = DebugInfo::stopClock('selected definitions');
  print "selected {$limit} definitions in {$millis} ms\n";

  foreach ($defs as $d) {
    $sumLength += mb_strlen($d->internalRep);
    $d->process();
    $html = HtmlConverter::convert($d);
  }

  $millis = DebugInfo::stopClock('processed definitions');
  $sumMillis += $millis;
  $remaining -= $limit;

  print "processed {$limit} definitions in {$millis} ms, {$remaining} definitions remaining\n";

} while ($remaining);

printf("%d definitions processed, %d characters, %d ms\n",
       SAMPLE_SIZE,
       $sumLength,
       $sumMillis);

printf("average: %.2f ms/definition\n", $sumMillis / SAMPLE_SIZE);
