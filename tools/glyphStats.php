<?php

require_once __DIR__ . '/../phplib/Core.php';

define('BATCH_SIZE', 10000);
define('EXCLUDE_SOURCES', [17, 42, 53]);
$offset = 0;
$modified = 0;

$opts = getopt('s:c:r:v');
$sourceId = $opts['s'] ?? null;
$commonThreshold = $opts['c'] ?? null;
$rareThreshold = $opts['r'] ?? null;
$verbose = isset($opts['v']);

if (!$sourceId || !$commonThreshold || !$rareThreshold) {
  usage('Missing mandatory argument.');
}

if (!($source = Source::get_by_id($sourceId))) {
  usage('Source does not exist.');
}

if ($commonThreshold < $rareThreshold) {
  usage('Common threshold must be at least equal to rare threshold.');
}

$baseGlyphs = array_fill_keys(Str::unicodeExplode(Source::BASE_GLYPHS), true);

$map = []; // maps glyphs to their frequencies
$defMap = []; // maps glyphs to sets of definition IDs, for rare and incorrect glyphs

do {
  $defs = Model::factory('Definition')
    ->where_in('status', [Definition::ST_ACTIVE, Definition::ST_HIDDEN])
    ->where('sourceId', $sourceId)
    ->order_by_asc('id')
    ->limit(BATCH_SIZE)
    ->offset($offset)
    ->find_many();

  foreach ($defs as $d) {
    // exclude footnotes and hidden comments
    $rep = preg_replace("/(\{\{.*\}\})|(▶.*◀)/U", '', $d->internalRep);
    $chars = Str::unicodeExplode($rep);

    foreach ($chars as $c) {
      if (!isset($baseGlyphs[$c])) {
        $map[$c] = 1 + ($map[$c] ?? 0);
        if ($map[$c] < $commonThreshold) {
          $defMap[$c][$d->id] = true;
        } else {
          $defMap[$c] = null;
        }
      }
    }
  }

  $offset += count($defs);
  if (count($defs)) {
    Log::info("$offset definitions reprocessed, $modified modified.");
  }
} while (count($defs));

if ($verbose) {
  arsort($map);
  foreach ($map as $char => $count) {
    printf ("[%s] unicode U+%04x count %d\n", $char, Str::unicodeOrd($char), $count);
  }
}

// sort glyphs by the unicode value
uksort($map, function($a, $b) {
  return Str::unicodeOrd($a) - Str::unicodeOrd($b);
});

$common = $rare = $incorrect = '';
foreach ($map as $char => $count) {
  if ($count >= $commonThreshold) {
    $common .= $char;
  } else if ($count >= $rareThreshold) {
    $rare .= $char;
  } else {
    $incorrect .= $char;
  }
}

print("Common glyphs: ---$common---\n");
print("Rare glyphs: ---$rare---\n");
print("Incorrect glyphs: ---$incorrect---\n");

// report definitions with rare and incorrect glyphs
if ($verbose) {
  foreach ($defMap as $glyph => $idMap) {
    if ($idMap) {
      printf("Definitions for glyph [%s] (unicode U+%04x):\n",
             $glyph, Str::unicodeOrd($glyph));
      foreach ($idMap as $id => $ignored) {
        print "  https://dexonline.ro/admin/definitionEdit.php?definitionId=$id\n";
      }
    }
  }
}

/*************************************************************************/

function usage($errMsg) {
  print <<<EOT
Collects statistics about glyphs used in a single source.

Mandatory arguments:

    -s <sourceId>       source ID
    -c <n>              minimum number of occurrences for a ghlyph to be considered common
    -r <n>              minimum number of occurrences for a ghlyph to be considered rare

Optional arguments:

    -v                  verbose output


EOT;
  print $errMsg . "\n";
  exit(1);
}
