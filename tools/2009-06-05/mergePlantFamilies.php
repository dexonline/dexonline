<?

require_once '../../phplib/util.php';

$PLURAL_INFLECTIONS = array(3, 11, 19, 27, 35);
$SOURCES = loadSources();
define('DRY_RUN', true);

$dbResult = mysql_query('select * from lexems where (lexem_model_type = "T") or (lexem_model_type in ("MQ", "FQ", "NQ") and lexem_restriction like "%P%")' .
                        'order by lexem_neaccentuat');
$found = 0;

while ($row = mysql_fetch_assoc($dbResult)) {
  $lexem = Lexem::createFromDbRow($row);
  $wordLists = WordList::loadByUnaccented($lexem->unaccented);
  $matchingWordLists = array();

  foreach ($wordLists as $wl) {
    if (in_array($wl->inflectionId, $PLURAL_INFLECTIONS) && $wl->lexemId != $lexem->id) {
      $matchingWordLists[] = $wl;
    }
  }

  if (count($matchingWordLists)) {
    $sources = getSourcesForLexem($lexem);
    print "{$lexem->unaccented} {$lexem->modelType}{$lexem->modelNumber}{$lexem->restriction} {$sources} " .
      "http://dexonline.ro/admin/lexemEdit.php?lexemId={$lexem->id}\n";
    foreach ($matchingWordLists as $wl) {
      $match = Lexem::load($wl->lexemId);
      $sources = getSourcesForLexem($match);
      print "    * {$match->unaccented} {$match->modelType}{$match->modelNumber}{$match->restriction} {$sources}" .
        "http://dexonline.ro/admin/lexemEdit.php?lexemId={$match->id}\n";
    }
    $found++;
  }
}
print "$found lexeme semnalate.\n";

/**************************************************************/

function loadSources() {
  $sources = Source::loadAllSources();
  $result = array();
  foreach ($sources as $source) {
    $result[$source->id] = $source;
  }
  return $result;
}

function getSourcesForLexem($lexem) {
  global $SOURCES;
  $defs = Definition::loadByLexemId($lexem->id);
  $sources = array();
  foreach ($defs as $def) {
    $shortName = $SOURCES[$def->sourceId]->shortName;
    if (!in_array($shortName, $sources)) {
      $sources[] = $shortName;
    }
  }
  return '(' . implode(',', $sources) . ')';
}
?>
