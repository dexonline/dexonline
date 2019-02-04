<?php
/* Htmlizes the definition, then builds the SimilarRecord */
require_once '../../lib/Core.php';
User::mustHave(User::PRIV_EDIT | User::PRIV_TRAINEE);

$definitionId = Request::get('definitionId');
$internalRep = Request::get('internalRep');
$sourceId = Request::get('sourceId');
$entryIds = Request::getArray('entryIds');

$d = ($definitionId)
   ? Definition::get_by_id($definitionId)
   : Model::factory('Definition')->create();
$d->internalRep = $internalRep;
$d->sourceId = $sourceId;
$d->process();

$html = HtmlConverter::convert($d);
$footnotes = $d->getFootnotes();

$sim = SimilarRecord::create($d, $entryIds);

SmartyWrap::assign('footnotes', $footnotes);
$footnoteHtml = SmartyWrap::fetch('bits/footnotes.tpl');

$data = $sim->getJsonFriendly();
$data['html'] = $html;
$data['footnoteHtml'] = $footnoteHtml;

print json_encode($data);
