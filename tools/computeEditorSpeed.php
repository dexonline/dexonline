<?php
require_once __DIR__ . '/../phplib/util.php';

define('DEFAULT_LIMIT', 1000);

/**
 * At what point do we determine that a definition was edited too slowly,
 * and thus the editor probably took a break? Expressed in characters per
 * second.
 **/
define('SLOW_LIMIT', 0.1);

$options = getopt('u:s::l::');

if (!isset($options['u'])) {
  die("Usage: {$argv[0]} -u=<username> [-s=<source_short_name>] [-l=<definition_limit>]\n");
}

$user = User::get_by_nick($options['u']);
if (!$user) {
  die("Unknown username {$options['u']}\n");
}

$source = null;
if (isset($options['s'])) {
  $source = Source::get_by_shortName($options['s']);
  if (!$source) {
    die("Unknown source {$options['s']}\n");
  }
}

$limit = DEFAULT_LIMIT;
if (isset($options['l'])) {
  $limit = $options['l'];
}

$defs = Model::factory('Definition')
  ->where_in('status', [ Definition::ST_ACTIVE, Definition::ST_HIDDEN ])
  ->where('userId', $user->id);

if ($source) {
  $defs = $defs->where('sourceId', $source->id);
}

$defs = $defs->order_by_desc('createDate')
  ->limit($limit)
  ->find_result_set();
Log::info("%d definitions match.", count($defs));

$prev = 0; // timestamp of the *next* definition in chronological order
$totalDefs = 0;
$totalTime = 0;
$totalChars = 0;
foreach ($defs as $d) {
  if ($prev) {
    $timeSpent = $prev - $d->createDate;
    if ($timeSpent) {
      $speed = mb_strlen($d->internalRep) / $timeSpent;
      $slow = ($speed < SLOW_LIMIT);
      Log::debug("[%s] [%s] [%s] time spent: %s seconds (%.2f chars/second) %s",
                 $d->createDate, $d->id, $d->lexicon, $timeSpent, $speed,
                 $slow ? "[SLOW]" : "");
      if (!$slow) {
        $totalDefs++;
        $totalTime += $timeSpent;
        $totalChars += mb_strlen($d->internalRep);
      }
    } else {
      Log::warning("Time spent is zero for definition ID {$d->id}");
    }
  }
  $prev = $d->createDate;
}
printf("definitions: %s (%s ignored); total time spent: %.3f days; total chars: %s (%.3f chars / second)\n",
       $totalDefs, count($defs) - $totalDefs, $totalTime / 86400, $totalChars,
       $totalChars / $totalTime);
