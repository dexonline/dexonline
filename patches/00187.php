<?php

// Identify all existing mentions and index them in the Mention table

$meanings = Model::factory('Meaning')
          ->where_raw('internalRep rlike "\\\\[\\\\d+\\\\]"')
          ->find_many();

foreach ($meanings as $m) {
  preg_match_all("/\\[\\[(\d+)\\]\\]/", $m->internalRep, $matches);
  $u = array_unique($matches[1]);
  Mention::wipeAndRecreate($m->id, Mention::TYPE_TREE, $u);

  preg_match_all("/(?<!\\[)\\[(\d+)\\](?!\\])/", $m->internalRep, $matches);
  $u = array_unique($matches[1]);
  Mention::wipeAndRecreate($m->id, Mention::TYPE_MEANING, $u);
}
