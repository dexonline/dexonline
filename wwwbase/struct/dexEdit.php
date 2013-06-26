<?php
require_once("../../phplib/util.php"); 
util_assertModerator(PRIV_EDIT);
util_assertNotMirror();

$lexemId = util_getRequestIntParameter('lexemId');
$hyphenations = util_getRequestParameter('hyphenations');
$pronounciations = util_getRequestParameter('pronounciations');
$variantIds = util_getRequestParameter('variantIds');
$jsonMeanings = util_getRequestParameter('jsonMeanings');

$lexem = Lexem::get_by_id($lexemId);
$mainVariant = Lexem::get_by_id($lexem->variantOf);

if ($jsonMeanings) {
  $meanings = json_decode($jsonMeanings);
  if ($mainVariant && !empty($meanings)) {
    FlashMessage::add("Acest lexem este o variantă a lui {$mainVariant} și nu poate avea el însuși sensuri.");
  } else {
    Meaning::saveTree($meanings, $lexem);
  }

  $lexem->hyphenations = $hyphenations;
  $lexem->pronounciations = $pronounciations;
  $lexem->save();

  // TODO: Add a validation routine that checks everything before saving anything
  // Save variants, but only if they meet certain criteria
  $variantIds = StringUtil::explode(',', $variantIds);
  if ($mainVariant && !empty($variantIds)) {
    FlashMessage::add("Acest lexem este o variantă a lui {$mainVariant} și nu poate avea el însuși variante.");
  } else {
    $lexem->updateVariants($variantIds);
  }

  util_redirect("dexEdit.php?lexemId={$lexem->id}");
}

$defs = Definition::loadByLexemId($lexem->id);
foreach ($defs as $def) {
  // $def->internalRep = AdminStringUtil::expandAbbreviations($def->internalRep, $def->sourceId);
}
$searchResults = SearchResult::mapDefinitionArray($defs);
$meaningTags = Model::factory('MeaningTag')->order_by_asc('value')->find_many();

SmartyWrap::assign('lexem', $lexem);
SmartyWrap::assign('meanings', Meaning::loadTree($lexem->id));
SmartyWrap::assign('meaningTags', $meaningTags);
SmartyWrap::assign('searchResults', $searchResults);
SmartyWrap::assign('variantOf', $mainVariant);
SmartyWrap::assign('variantIds', $lexem->getVariantIds());
SmartyWrap::assign('pageTitle', "Editare lexem: {$lexem->formNoAccent}");
SmartyWrap::addCss('jqueryui', 'easyui', 'select2', 'struct', 'flex');
SmartyWrap::addJs('dex', 'jquery', 'easyui', 'jqueryui', 'select2', 'struct');
SmartyWrap::displayWithoutSkin('struct/dexEdit.ihtml');

?>
