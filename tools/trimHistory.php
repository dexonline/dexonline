#!/usr/bin/php
<?php

/**
 * Trim DefinitionHistory by removing identical consecutive versions. Does not
 * take modUserId into account; if the change is empty, it's empty regardless
 * who made it.
 **/

ini_set('memory_limit','1G');

require_once __DIR__ . '/../lib/Core.php';

DB::setBuffering(false);
$result = DB::execute('select * from DefinitionVersion', PDO::FETCH_ASSOC);

// Maps definitionId => pairs of [ id, md5 ]. Pairs are concatenated to save memory/
$map = [];

$i = 0;
foreach ($result as $row) {
  $str = sprintf(
    '%d|%d|%s|%s|%d|%d',
    $row['action'], $row['sourceId'], $row['lexicon'],
    $row['internalRep'], $row['status'], $row['createDate']
  );
  if (isset($map[$row['definitionId']])) {
    $map[$row['definitionId']] .= '|' . $row['id'] . '|' . md5($str);
  } else {
    $map[$row['definitionId']] = $row['id'] . '|' . md5($str);
  }

  $i++;
  if ($i % 100000 == 0) {
    printf("%d versions read, memory usage %dM\n", $i, memUsed());
  }
}

$i = 0;
$numDeleted = 0;
$deleteIds = []; // perform deletions in batches
foreach ($map as $definitionId => $versionString) {
  $versions = parseVersionString($versionString);

  // It *looks* like versions are already in ID order, but I'm not sure.
  ksort($versions);

  $prevId = null;
  $prevHash = null;
  foreach ($versions as $id => $hash) {
    if ($prevId && ($hash == $prevHash)) {
      $deleteIds[] = $id;
      $numDeleted++;

      if (count($deleteIds) == 500) {
        deleteVersions($deleteIds);
        $deleteIds = [];
      }
    } else {
      $prevId = $id;
      $prevHash = $hash;
    }
  }

  $i++;
  if ($i % 50000 == 0) {
    printf("%d/%d definitions scanned, %d versions deleted, memory usage %dM\n",
           $i, count($map), $numDeleted, memUsed());
  }
}

deleteVersions($deleteIds);

/*************************************************************************/

function memUsed() {
  return memory_get_usage() / (1 << 20);
}

function parseVersionString($str) {
  $parts = explode('|', $str);

  $result = [];
  for ($i = 0; $i < count($parts); $i += 2) {
    $result[(int)$parts[$i]] = $parts[$i + 1];
  }

  return $result;
}

function deleteVersions($ids) {
  if (!empty($ids)) {
    $query = sprintf('delete from DefinitionVersion where id in (%s)',
                     implode(',', $ids));
    DB::execute($query);
  }
}
