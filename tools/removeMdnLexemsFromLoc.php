<?

/**
 * Remove from LOC all the lexems which
 * - are associated with definitions from DN or MDN, and
 * - are not associated with definitions from DEX (all editions), DEX-S, DMLR, DLRM, DOOM or DOOM-2, and
 * - are not infinitives or participles of lexems in the above sources.
 **/

require_once('../phplib/util.php');

$goodSourceIds = array(Source::get("shortName = \"DEX '75\"")->id,
                       Source::get("shortName = \"DEX '84\"")->id,
                       Source::get("shortName = \"DEX '96\"")->id,
                       Source::get("shortName = \"DEX '98\"")->id,
                       Source::get("shortName = \"DEX '09\"")->id,
                       Source::get("shortName = 'DEX-S'")->id,
                       Source::get("shortName = 'DMLR'")->id,
                       Source::get("shortName = 'DLRM'")->id,
                       Source::get("shortName = 'DOOM'")->id,
                       Source::get("shortName = 'DOOM 2'")->id);

// Select all the LOC lexems associated with definitions from DN / MDN.
// Process verbs first because we will need to look at their participles / long infinitives later.
$query = "select distinct Lexem.* from Lexem, LexemDefinitionMap, Definition where Lexem.id = LexemDefinitionMap.lexemId and LexemDefinitionMap.definitionId = Definition.id " .
  "and Lexem.isLoc and Definition.sourceId in (17, 21) order by Lexem.modelType in ('V', 'VT') desc, Lexem.formNoAccent";
$lexems = db_getObjects(new Lexem(), db_execute($query));

foreach($lexems as $l) {
  $definitions = Definition::loadByLexemId($l->id);
  $hasGoodSourceIds = false;
  $i = 0;
  while ($i < count($definitions) && !$hasGoodSourceIds) {
    $hasGoodSourceIds = in_array($definitions[$i]->sourceId, $goodSourceIds);
    $i++;
  }
  if (!$hasGoodSourceIds) {
    // Check if the lexem is a long infinitive or a participle
    $numVerbs = db_getSingleValue("select count(distinct Lexem.id) from Lexem, InflectedForm where Lexem.id = InflectedForm.lexemId and Lexem.isLoc " .
                                  "and InflectedForm.formNoAccent = '{$l->formNoAccent}' and InflectedForm.inflectionId in (50, 52)");
    if ($numVerbs) {
      print "Keeping http://dexonline.ro/lexem/{$l->formNoAccent}/{$l->id}\n";
    } else {
      // Clear the isLoc bit
      print "http://dexonline.ro/lexem/{$l->formNoAccent}/{$l->id}\n";
      $l->isLoc = false;
      $l->save();
    }
  }
}

?>
