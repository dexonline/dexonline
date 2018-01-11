<?php
/* Htmlizes the definition and comment, then builds the SimilarRecord */
require_once("../../phplib/Core.php");
User::mustHave(User::PRIV_EDIT);
Util::assertNotMirror();

$definitionId = Request::get('definitionId');
$definitionInternalRep = Request::get('definitionInternalRep');
$commentInternalRep = Request::get('commentInternalRep');
$sourceId = Request::get('sourceId');
$entryIds = Request::getArray('entryIds');

$d = Definition::get_by_id($definitionId);
$d->internalRep = StringUtil::sanitize($definitionInternalRep, $sourceId);
$d->htmlRep = StringUtil::htmlize($d->internalRep, $sourceId);
$d->sourceId = $sourceId;

$commentInternalRep = StringUtil::sanitize($commentInternalRep, $sourceId);
$commentHtmlRep = StringUtil::htmlize($commentInternalRep, $sourceId);

$sim = SimilarRecord::create($d, $entryIds);

$data = $sim->getJsonFriendly();
$data['htmlRep'] = $d->htmlRep;
$data['commentHtmlRep'] = $commentHtmlRep;

print json_encode($data);

?>
