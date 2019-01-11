<?php
require_once("../../phplib/Core.php");
User::mustHave(User::PRIV_EDIT | User::PRIV_TRAINEE);
Util::assertNotMirror();

$def = Model::factory('Definition')->create();
$def->id = Request::get('definitionId');
$def->internalRep = Request::get('internalRep');
$def->sourceId = Request::get('sourceId');

$def->extractLexicon();

if ($def->lexicon) {
  $entries = Model::factory('Definition')
    ->table_alias('d')
    ->select('ed.entryId')
    ->distinct()
    ->join('EntryDefinition', ['d.id', '=', 'ed.definitionId'], 'ed')
    ->where('d.lexicon', $def->lexicon)
    ->where_not_equal('d.id', $def->id)
    ->limit(10)
    ->find_many();
  $entryIds = Util::objectProperty($entries, 'entryId');
} else {
  $entryIds = [];
}

header('Content-Type: application/json');
echo json_encode($entryIds);
