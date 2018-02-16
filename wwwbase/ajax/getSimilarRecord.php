<?php
/* Htmlizes the definition, then builds the SimilarRecord */
require_once("../../phplib/Core.php");
User::mustHave(User::PRIV_EDIT);
Util::assertNotMirror();

$definitionId = Request::get('definitionId');
$internalRep = Request::get('internalRep');
$sourceId = Request::get('sourceId');
$entryIds = Request::getArray('entryIds');

$d = Definition::get_by_id($definitionId);
$d->internalRep = $internalRep;
$d->sourceId = $sourceId;
$footnotes = $d->process(false);

$sim = SimilarRecord::create($d, $entryIds);

$data = $sim->getJsonFriendly();
$data['htmlRep'] = $d->htmlRep;
$data['footnotes'] = [];
foreach ($footnotes as $f) {
  $data['footnotes'][] = $f->htmlRep;
}

print json_encode($data);
