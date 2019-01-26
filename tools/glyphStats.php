<?php

require_once __DIR__ . '/../phplib/Core.php';

define('BATCH_SIZE', 10000);
define('EXCLUDE_SOURCES', [17, 42, 53]);

$opts = getopt('s:c:r:vwd');
$sourceId = $opts['s'] ?? null;
$commonThreshold = $opts['c'] ?? null;
$rareThreshold = $opts['r'] ?? null;
$verbose = isset($opts['v']);
$write = isset($opts['w']);
$updateDefs = isset($opts['d']);

// validation
if (!$sourceId || !$commonThreshold || !$rareThreshold) {
  usage('Missing mandatory argument.');
}

if (!($source = Source::get_by_id($sourceId))) {
  usage('Source does not exist.');
}

if ($commonThreshold < $rareThreshold) {
  usage('Common threshold must be at least equal to rare threshold.');
}

// scan the definitions; collect glyph counts and definitions with
// rare/incorrect glyphs
$base = array_fill_keys(Str::unicodeExplode(Source::BASE_GLYPHS), true);

$map = []; // maps glyphs to their frequencies
$defMap = []; // maps glyphs to sets of definition IDs, for rare and incorrect glyphs

scanDefinitions($source, 'collectGlyphStats');

if ($verbose) {
  // print glyph statistics
  arsort($map);
  foreach ($map as $glyph => $count) {
    printf ("[%s] unicode U+%04x count %d\n", $glyph, Str::unicodeOrd($glyph), $count);
  }

  // report definitions with rare and incorrect glyphs
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

// assemble the common, rare and incorrect glyph strings
$common = [];
$rare = [];
$incorrect = [];
foreach ($map as $glyph => $count) {
  if ($count >= $commonThreshold) {
    $common[$glyph] = true;
  } else if ($count >= $rareThreshold) {
    $rare[$glyph] = true;
  } else {
    $incorrect[$glyph] = true;
  }
}

$commonString = implode(array_keys($common));
$rareString = implode(array_keys($rare));
$incorrectString = implode(array_keys($incorrect));

printf("Common glyphs: ---{$commonString}---\n");
printf("Rare glyphs: ---{$rareString}---\n");
printf("Incorrect glyphs: ---{$incorrectString}---\n");

// update the Source record fields
if ($write) {
  $source->commonGlyphs = $commonString;
  $source->rareGlyphs = $rareString;
  $source->save();
}

if ($updateDefs) {
  // update the suspiciousGlyphs fields
  $defIdsForTagging = [];
  scanDefinitions($source, 'updateDefinition');

  // remove existing [rare glyphs] tags
  $tagId = Config::get('tags.rareGlyphsTagId');
  DB::execute(sprintf(
    'delete ot ' .
    'from ObjectTag ot ' .
    'join Definition d on ot.objectType = %d and ot.objectId = d.id ' .
    'where d.sourceId = %d ' .
    'and ot.tagId = %d',
    ObjectTag::TYPE_DEFINITION, $source->id, $tagId));

  // tag newly discovered definitions
  foreach ($defIdsForTagging as $defId) {
    ObjectTag::associate(ObjectTag::TYPE_DEFINITION, $defId, $tagId);
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
    -w                  write computed glyph sets to the Source fields
    -d                  recompute the suspiciousGlyphs field and [rare glyphs] tag on all definitions


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
  global $rare, $incorrect, $defIdsForTagging;

  $suspicious = [];
  $hasIncorrect = false;

  // suspicious glyphs = rare glyphs + incorrect glyphs
  foreach ($chars as $c) {
    if (isset($rare[$c])) {
      $suspicious[$c] = true;
    } else if (isset($incorrect[$c])) {
      $suspicious[$c] = true;
      $hasIncorrect = true;
    }
  }
  $suspiciousGlyphs = implode(array_keys($suspicious));
  if ($suspiciousGlyphs != $d->suspiciousGlyphs) {
    $d->suspiciousGlyphs = $suspiciousGlyphs;
    $d->save();
  }

  // the definition needs the [rare glyphs] tag if it contains rare glyphs,
  // but no incorrect glyphs
  if ($d->suspiciousGlyphs && !$hasIncorrect) {
    $defIdsForTagging[] = $d->id;
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
