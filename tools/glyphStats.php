<?php

require_once __DIR__ . '/../phplib/Core.php';

define('BATCH_SIZE', 10000);
define('EXCLUDE_SOURCES', [17, 42, 53]);

$opts = getopt('s:c:r:vwd');
$sourceId = $opts['s'] ?? null;
$commonThreshold = $opts['c'] ?? null;
$verbose = isset($opts['v']);
$write = isset($opts['w']);
$updateDefs = isset($opts['d']);

// validation
if (!$sourceId || !$commonThreshold) {
  usage('Missing mandatory argument.');
}

if (!($source = Source::get_by_id($sourceId))) {
  usage('Source does not exist.');
}

// scan the definitions; collect glyph counts and definitions with rare glyphs
$base = array_fill_keys(Str::unicodeExplode(Source::BASE_GLYPHS), true);

$map = []; // maps glyphs to their frequencies
$defMap = []; // maps glyphs to sets of definition IDs, for rare glyphs

scanDefinitions($source, 'collectGlyphStats');

if ($verbose) {
  // print glyph statistics
  arsort($map);
  foreach ($map as $glyph => $count) {
    printf ("[%s] unicode U+%04x count %d\n", $glyph, Str::unicodeOrd($glyph), $count);
  }

  // report definitions with rare glyphs
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

// sort glyphs by the unicode value
uksort($map, function($a, $b) {
  return Str::unicodeOrd($a) - Str::unicodeOrd($b);
});

// assemble the common and rare glyph strings
$common = [];
$rare = [];
foreach ($map as $glyph => $count) {
  if ($count >= $commonThreshold) {
    $common[$glyph] = true;
  } else {
    $rare[$glyph] = true;
  }
}

$commonString = implode(array_keys($common));
$rareString = implode(array_keys($rare));

printf("Common glyphs: ---{$commonString}---\n");
printf("Rare glyphs: ---{$rareString}---\n");

// update the Source.commonGlyphs field
if ($write) {
  $source->commonGlyphs = $commonString;
  $source->save();
}

if ($updateDefs) {
  // update the rareGlyphs field and [rare glyphs] tag
  $tagId = Config::get('tags.rareGlyphsTagId');
  scanDefinitions($source, 'updateDefinition');
}

/*************************************************************************/

function usage($errMsg) {
  print <<<EOT
Collects statistics about glyphs used in a single source.

Mandatory arguments:

    -s <sourceId>       source ID
    -c <n>              minimum number of occurrences for a ghlyph to be considered common

Optional arguments:

    -v                  verbose output
    -w                  write computed glyph sets to the Source fields
    -d                  recompute the rareGlyphs field and [rare glyphs] tag on all definitions


EOT;
  print $errMsg . "\n";
  exit(1);
}

function collectGlyphStats($d, $chars) {
  global $base, $commonThreshold, $defMap, $map;

  foreach ($chars as $c) {
    if (!isset($base[$c])) {
      $map[$c] = 1 + ($map[$c] ?? 0);
      if ($map[$c] < $commonThreshold) {
        $defMap[$c][$d->id] = true;
      } else {
        // glyph has become common; delete its definition list
        $defMap[$c] = null;
      }
    }
  }
}

function updateDefinition($d, $chars) {
  global $rare, $tagId;

  $rareGlyphs = [];
  foreach ($chars as $c) {
    if (isset($rare[$c])) {
      $rareGlyphs[$c] = true;
    }
  }
  $rareGlyphs = implode(array_keys($rareGlyphs));

  if ($rareGlyphs != $d->rareGlyphs) {
    $d->rareGlyphs = $rareGlyphs;
    $d->save();
    ObjectTag::dissociate(ObjectTag::TYPE_DEFINITION, $d->id, $tagId);
  }
}

function scanDefinitions($source, $callback) {
  $offset = 0;

  do {
    $defs = Model::factory('Definition')
      ->where_in('status', [Definition::ST_ACTIVE, Definition::ST_HIDDEN])
      ->where('sourceId', $source->id)
      ->order_by_asc('id')
      ->limit(BATCH_SIZE)
      ->offset($offset)
      ->find_many();

    foreach ($defs as $d) {
      // exclude footnotes and hidden comments
      $rep = preg_replace("/(\{\{.*\}\})|(▶.*◀)/U", '', $d->internalRep);
      $chars = Str::unicodeExplode($rep);

      call_user_func($callback, $d, $chars);
    }

    $offset += count($defs);
    if (count($defs)) {
      Log::info("$offset definitions examined.");
    }
  } while (count($defs));
}
