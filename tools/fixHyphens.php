<?php
require_once('../phplib/util.php');

$START_ID = 324821;
$COMMON_WORDS = array('a-i', 'a-și', 'de-o', 'i-e', 'l-a', 'n-am', 'n-ar', 'nu-l', 'nu-ți', 's-a', 'și-a', 'ne-a', 's-au', 'l-au', 'i-a', 'să-i',
                      'ce-ai', 'i-au', 'n-a', 'm-a', 'a-l', 'ne-am', 'm-au', 'de-a', 'le-a', 'n-ai', 'i-ar', 'ți-e', 'ce-a', 'ce-ți', 'l-am',
                      'dintr-un', 'dintr-o', 'într-un', 'într-o', 'printr-un', 'printr-o');
$dbResult = db_execute("select * from Definition where sourceId in (6,7,8,9) and Status = 0 and id >= $START_ID order by id");
$numDefinitions = $dbResult->RowCount();

$count = 0;
while (!$dbResult->EOF) {
  $d = new Definition();
  $d->set($dbResult->fields);
  $dbResult->MoveNext();

  $rep = $d->internalRep;
  $oldPos = -1;
  while (($pos = mb_strpos($rep, '-', $oldPos + 1)) !== FALSE) {
    $len = mb_strlen($rep);
    $first = $pos;
    while (text_isUnicodeLetter(text_getCharAt($rep, $first - 1))) {
      $first--;
    }
    $last = $pos + 1;
    while (text_isUnicodeLetter(text_getCharAt($rep, $last)) && $last < $len) {
      $last++;
    }
    $part1 = mb_substr($rep, $first, $pos - $first);
    $part2 = mb_substr($rep, $pos + 1, $last - $pos - 1);

    if ($part1 && $part2) {
      // See if this is a common composite word like 's-a' or 'și-a'
      if (!in_array(mb_strtolower("{$part1}-{$part2}"), $COMMON_WORDS)) {
        // See if this is a gerund
        if (!text_endsWith($part1, 'ndu') || ($part2 != 'și' && $part2 != 'se' && $part2 != 'l')) {
          // See if there is a "sil." indication right before, in which case skip this part
          $silStart = max(0, $first - 20);
          if (mb_stripos(mb_substr($rep, $silStart, 20), 'sil.') === false) {
            $if = new InflectedForm();
            $forms1 = $if->find("formNoAccent = '{$part1}'");
            $forms2 = $if->find("formNoAccent = '{$part2}'");
            $formsJoin = $if->find("formNoAccent = '{$part1}{$part2}'");
            $partsMatch = (count($forms1) ? 1 : 0) + (count($forms2) ? 1 : 0);
            $jointMatch = count($formsJoin) ? 1 : 0;
            
            print "id:{$d->id} [{$part1}-{$part2}]     http://dexonline.ro/admin/definitionEdit.php?definitionId={$d->id} ";
            if ($partsMatch == 1 || $jointMatch == 1) {
              print "      JOIN? (y/n) ";
              $command = fgets(STDIN);
              if ($command == "y\n") {
                print "  Old rep: [$rep]\n";
                $rep = mb_substr($rep, 0, $pos) . mb_substr($rep, $pos + 1);
                print "  New rep: [$rep]\n";
              }
            } else {
              print "SKIPPING\n";
            }
          }
        }
      }
    }

    $oldPos = $pos;
  }
  if ($rep != $d->internalRep) {
    print "  Definition {$d->id} has changed, saving.\n";
    $d->internalRep = $rep;
    $d->htmlRep = text_htmlize($d->internalRep);
    $d->save();
  }

  $count++;
  if ($count % 1000 == 0) {
    print "Fixed $count/$numDefinitions definitions.\n";
  }
}

?>
