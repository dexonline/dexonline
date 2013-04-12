<?php
require_once("../../phplib/util.php"); 
util_assertModerator(PRIV_EDIT);
util_assertNotMirror();

$lexemId = util_getRequestIntParameter('lexemId');
$jsonMeanings = util_getRequestParameter('jsonMeanings');

$lexem = Lexem::get_by_id($lexemId);

if ($jsonMeanings) {
  $meanings = json_decode($jsonMeanings);
  $seenMeaningIds = array();

  // Keep track of the previous meaning ID at each level. This allows us to populate the parentId field
  $meaningStack = array();
  $displayOrder = 1;
  foreach ($meanings as $tuple) {
    $m = $tuple->id ? Meaning::get_by_id($tuple->id) : Model::factory('Meaning')->create();
    $m->parentId = $tuple->level ? $meaningStack[$tuple->level - 1] : 0;
    $m->displayOrder = $displayOrder++;
    $m->userId = session_getUserId();
    $m->lexemId = $lexem->id;
    $m->internalRep = $tuple->internalRep;
    $m->htmlRep = AdminStringUtil::htmlize($m->internalRep, 0);
    $m->internalComment = $tuple->internalComment;
    $m->htmlComment = AdminStringUtil::htmlize($m->internalComment, 0);
    $m->save();
    $meaningStack[$tuple->level] = $m->id;

    $sourceIds = StringUtil::explode(',', $tuple->sourceIds);
    MeaningSource::updateMeaningSources($m->id, $sourceIds);
    $meaningTagIds = StringUtil::explode(',', $tuple->meaningTagIds);
    MeaningTagMap::updateMeaningTags($m->id, $meaningTagIds);
    $synonymIds = StringUtil::explode(',', $tuple->synonymIds);
    Synonym::updateList($m->id, $synonymIds, Synonym::TYPE_SYNONYM);
    $antonymIds = StringUtil::explode(',', $tuple->antonymIds);
    Synonym::updateList($m->id, $antonymIds, Synonym::TYPE_ANTONYM);
    $seenMeaningIds[] = $m->id;
  }
  Meaning::deleteNotInSet($seenMeaningIds, $lexem->id);

  util_redirect("dexEdit.php?lexemId={$lexem->id}");
}

$defs = Definition::loadByLexemId($lexem->id);
foreach ($defs as $def) {
  $def->internalRep = AdminStringUtil::expandAbbreviations($def->internalRep, $def->sourceId);
}
$searchResults = SearchResult::mapDefinitionArray($defs);
$meaningTags = Model::factory('MeaningTag')->order_by_asc('value')->find_many();

SmartyWrap::assign('lexem', $lexem);
SmartyWrap::assign('meanings', Meaning::loadTree($lexem->id));
SmartyWrap::assign('meaningTags', $meaningTags);
SmartyWrap::assign('searchResults', $searchResults);
SmartyWrap::assign('sectionTitle', "Editare lexem: {$lexem->formNoAccent}");
SmartyWrap::addCss('jqueryui', 'easyui', 'select2');
SmartyWrap::addJs('jquery', 'easyui', 'jqueryui', 'select2');
SmartyWrap::displayAdminPage('admin/dexEdit.ihtml');

?>
