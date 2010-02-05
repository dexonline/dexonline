<?
require_once('../../phplib/util.php');
assert_options(ASSERT_BAIL, 1);
debug_off();

$dbResult = mysql_query("select * from lexems where lexem_id not in " .
                        "(select LexemId from LexemDefinitionMap, Definition " .
                        "where DefinitionId = Definition.Id " .
                        "and SourceId not in (6, 8) and Status = 0)");

$seen = 0;
$split = 0;

while (($dbRow = mysql_fetch_assoc($dbResult)) != null) {
  $l = Lexem::createFromDbRow($dbRow);
  $defs = Definition::loadByLexemId($l->id);

  if (!count($defs)) {
    continue;
  }

  $seen++;

  // Remove the -ul accent where possible.
  if (text_endsWith($l->form, 'ul')) {
    $form = substr($l->form, 0, strlen($l->form) - 2);
    $otherLexems = Lexem::loadByForm($form);
    if (count($otherLexems)) {
      print "REMOVING -UL FROM: {$l->form}\n";
      foreach ($otherLexems as $otherLexem) {
        foreach ($defs as $def) {
          LexemDefinitionMap::associate($otherLexem->id, $def->id);
        }
      }
      $l->delete();
      $split++;
      continue;
    }
  }

  // Split the word in two, if it leads to two existing lexems, and if both
  // have at least three letters.
  if (mb_strlen($l->unaccented) >= 8 &&
      (text_endsWith($l->form, 'lui') ||
       text_endsWith($l->form, 'ei') ||
       text_endsWith($l->form, 'ii') ||
       text_endsWith($l->form, 'elor') ||
       text_endsWith($l->form, 'ilor') ||
       text_endsWith($l->form, 'asă') ||
       text_endsWith($l->form, 'scă'))) {
    $len = mb_strlen($l->unaccented);
    for ($splitPoint = 3; $splitPoint <= $len - 3; $splitPoint++) {
      $word1 = mb_substr($l->unaccented, 0, $splitPoint);
      $word2 = mb_substr($l->unaccented, $splitPoint);
      $l1 = getNouns($word1);
      $l2 = getNouns($word2);
      if (count($l1) && count($l2)) {
        print "[{$l->form}] [$word1] [$word2]\n";
        $all = array_merge($l1, $l2);
        foreach ($defs as $def) {
          foreach ($all as $newLexem) {
            LexemDefinitionMap::associate($newLexem->id, $def->id);
          }
          // Also fix the definition if it is missing a hyphen.
          $firstAt = strpos($def->internalRep, '@');
          $secondAt = strpos($def->internalRep, '@', $firstAt + 1);
          assert($firstAt === 0);
          assert($secondAt !== false);
          $text = trim(substr($def->internalRep, $firstAt + 1,
                              $secondAt - $firstAt - 1));
          $normText = text_removeAccents(text_unicodeToLower($text));
          if ($normText == $l->unaccented) {
            $def->internalRep = text_insert($def->internalRep, '-',
                                            $splitPoint + $firstAt + 1);
            $def->htmlRep = text_htmlize($def->internalRep);
            print "    [{$def->internalRep}]\n";
            $def->save();
          }
        }
        $l->delete();
        $split++;
        break; // Skip other split points
      }
    }
  }

  if (mb_strlen($l->unaccented) >= 8 && text_contains($l->form, 'de')) {
    $parts = split('de', $l->unaccented);
    if (count($parts) == 2) {
      $word1 = $parts[0];
      $word2 = $parts[1];
      $l1 = getNouns($word1);
      $l2 = getNouns($word2);
      if (count($l1) && count($l2)) {
        print "[{$l->form}] [$word1] [$word2]\n";
        $all = array_merge($l1, $l2);
        foreach ($defs as $def) {
          foreach ($all as $newLexem) {
            LexemDefinitionMap::associate($newLexem->id, $def->id);
          }
          // Also fix the definition if it is missing hyphens.
          $firstAt = strpos($def->internalRep, '@');
          $secondAt = strpos($def->internalRep, '@', $firstAt + 1);
          assert($firstAt === 0);
          assert($secondAt !== false);
          $text = trim(substr($def->internalRep, $firstAt + 1,
                              $secondAt - $firstAt - 1));
          $normText = text_removeAccents(text_unicodeToLower($text));
          if ($normText == $l->unaccented) {
            $def->internalRep = text_insert($def->internalRep, '-',
                                            $firstAt + 3 + mb_strlen($word1));
            $def->internalRep = text_insert($def->internalRep, '-',
                                            $firstAt + 1 + mb_strlen($word1));
            $def->htmlRep = text_htmlize($def->internalRep);
            print "    [{$def->internalRep}]\n";
            $def->save();
          }
        }
        $l->delete();
        $split++;
      }
    }
  }

  // See if any definitions hint at the hyphenation
  $foundHyphenation = false;
  foreach ($defs as $def) {
    if ($foundHyphenation || $def->status != ST_ACTIVE) {
      continue;
    }
    $firstAt = strpos($def->internalRep, '@');
    $secondAt = strpos($def->internalRep, '@', $firstAt + 1);
    assert($firstAt === 0);
    assert($secondAt !== false);
    $text = trim(substr($def->internalRep, $firstAt + 1,
                        $secondAt - $firstAt - 1));
    $normText = text_removeAccents(text_unicodeToLower($text));
    if (text_contains($normText, '-') &&
        str_replace('-', '', $normText) == $l->unaccented) {
      print "[{$l->unaccented}] [$normText]\n";
      $parts = split('-', $normText);
      foreach ($parts as $part) {
        $lexems = Lexem::searchWordlists($part, true);
        if (!count($lexems)) {
          print "Creez lexemul [$part]\n";
          $lexem = Lexem::create($part, 'T', '1', '');
          $lexem->comment = 'Creat pentru despărțirea în cuvinte a unui ' .
            'alt lexem';
          $lexem->save();
          $lexem->id = db_getLastInsertedId();
          $lexem->regenerateParadigm();
          $lexems[] = $lexem;
        }

        // Now associate every lexem with every definition
        foreach ($defs as $defAssoc) {
          foreach ($lexems as $lexemAssoc) {
            LexemDefinitionMap::associate($lexemAssoc->id, $defAssoc->id);
          }
        }
      }
      foreach ($defs as $fixDef) {
        $fixFirstAt = strpos($fixDef->internalRep, '@');
        $fixSecondAt = strpos($fixDef->internalRep, '@', $fixFirstAt + 1);
        assert($fixFirstAt === 0);
        assert($fixSecondAt !== false);
        $fixText = trim(substr($fixDef->internalRep, $fixFirstAt + 1,
                               $fixSecondAt - $fixFirstAt - 1));
        if (!text_contains($fixText, '-') &&
            !text_contains($fixText, ' ') &&
            str_replace('-', '', $normText) == 
            text_unicodeToLower(text_removeAccents($fixText))) {

          $prevPos = 0;
          while (($pos = mb_strpos($normText, '-', $prevPos)) !== false) {
            $fixText = text_insert($fixText, '-', $pos);
            $prevPos = $pos + 1;
          }
          $fixDef->internalRep = substr($fixDef->internalRep, 0,
                                        $fixFirstAt + 1) .
            $fixText . substr($fixDef->internalRep, $fixSecondAt);
          $fixDef->htmlRep = text_htmlize($fixDef->internalRep);
          $fixDef->save();
          print ("    [{$fixDef->internalRep}]\n");
        }
      }
      $split++;
      $l->delete();
      $foundHyphenation = true;
    }
  }

  //print "NOT OK: {$l->unaccented}\n";
}
  
print "Seen $seen lexems, split $split lexems.\n";

function getNouns($word) {
  $lexems = Lexem::searchWordlists($word, true);
  $result = array();
  foreach ($lexems as $l) {
    if ($l->modelType == 'M' || $l->modelType == 'F' || $l->modelType == 'N' ||
        $l->modelType == 'A' || $l->modelType == 'MF' ) {
      $result[] = $l;
    }
  }
  return $result;
}

?>
