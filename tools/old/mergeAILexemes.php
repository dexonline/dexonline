<?php

// Merges lexems/entries using the pre-1993 orthography (î) into post-1993 lexems (â).
// Merges even proper nouns. If a proper noun exists in both forms, it is highly unlikely that
// both forms are correct. Thus, it is easier to correct a single form in the future, if needed.

require_once __DIR__ . '/../../phplib/util.php';

Log::notice('started');

$lexems = Model::factory('Lexem')
        ->where_raw('binary formNoAccent like "%â%"')
        ->order_by_asc('formNoAccent')
        ->find_many();

$i = 0;
foreach ($lexems as $l) {
  $iform = preg_replace("/(?<=[A-Za-zĂȘȚŞŢășşțţ])â(?=[A-Za-zĂȘȚŞŢășşțţ])/",
                        "$1î$2", $l->formNoAccent);
  $iform = preg_replace("/(r[ou]m)î(n)/i", "\${1}â\${2}", $iform);

  if ($iform != $l->formNoAccent) {
    $candidates = Model::factory('Lexem')
                ->where_raw("formNoAccent = binary '{$iform}'")
                ->find_many();
    foreach ($candidates as $c) {
      if ($c->isLoc) {
        Log::warning("Skipping {$c} (ID: {$c->id}) because isLoc = 1");
      } else {
        print "merging {$c} (ID: {$c->id}, entry: {$c->entryId}) into {$l} (ID: {$l->id})\n";

        // Delete empty trees
        $tes = TreeEntry::get_all_by_entryId($c->entryId);
        foreach ($tes as $te) {
          $t = Tree::get_by_id($te->treeId);
          $meanings = Meaning::get_all_by_treeId($t->id);
          if (!count($meanings)) {
            Relation::delete_all_by_treeId($t->id);
            TreeEntry::delete_all_by_treeId($t->id);
            $t->delete();
          }
        }

        // Merge entries
        if ($c->entryId != $l->entryId) {
          $e = Entry::get_by_id($c->entryId);
          $e->mergeInto($l->entryId);
        }

        $c->delete();
      }
    }
  }

  if (++$i % 100 == 0) {
    Log::info('processed %d/%d lexems', $i, count($lexems));
  }
}

Log::notice('finished');
