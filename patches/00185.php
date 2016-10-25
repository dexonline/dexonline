<?php

// Take all lexems having the [adjective] and [masculine noun] tags,
// but not the [feminine noun] tag, from the DOR source, having definitions
// that also contain feminine forms.
//
// Tag these lexems with the [feminine noun] tag.

// not used, but defined here for clarity
define('BATCH_SIZE', 1000);
define('DOR_SOURCE_ID', 38);
define('ADJECTIVE_TAG_ID', 45);
define('MASCULINE_NOUN_TAG_ID', 50);
define('FEMININE_NOUN_TAG_ID', 51);

$lexems = Model::factory('Lexem')->raw_query(
  "select l.*, " .
  "sum(ot.tagId = 45) as adj, " .
  "sum(ot.tagId = 50) as masc, " .
  "sum(ot.tagId = 51) as fem " .
  "from Lexem l " .
  "join ObjectTag ot on l.id = ot.objectId " .
  "join EntryDefinition ed on ed.entryId = l.entryId " .
  "join Definition d on ed.definitionId = d.id " .
  "where ot.objectType = 2 " .
  "and d.sourceId = 38 " .
  "and d.status in (0, 3) " .
  "and d.internalRep like '%adj.%' " .
  "and d.internalRep like '%s. m.%f. sg.%' " .
  "group by l.id " .
  "having adj and masc and not fem " .
  "order by l.formNoAccent"
)->find_many();

foreach ($lexems as $l) {
  $ot = Model::factory('ObjectTag')->create();
  $ot->objectId = $l->id;
  $ot->objectType = ObjectTag::TYPE_LEXEM;
  $ot->tagId = FEMININE_NOUN_TAG_ID;
  $ot->save();
}

Log::info("Tagged %d lexems.", count($lexems));
