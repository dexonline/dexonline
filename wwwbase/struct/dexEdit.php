<?Php
require_once("../../phplib/util.php"); 
util_assertModerator(PRIV_EDIT);
util_assertNotMirror();

$lexemId = util_getRequestIntParameter('lexemId');
$hyphenations = util_getRequestParameter('hyphenations');
$pronunciations = util_getRequestParameter('pronunciations');
$variantIds = util_getRequestCsv('variantIds');
$variantOfId = util_getRequestParameter('variantOfId');
$jsonMeanings = util_getRequestParameter('jsonMeanings');

$lexem = Lexem::get_by_id($lexemId);

if ($jsonMeanings) {
  $lexem->hyphenations = $hyphenations;
  $lexem->pronunciations = $pronunciations;
  $lexem->variantOfId = $variantOfId ? $variantOfId : null;
  $variantOf = Lexem::get_by_id($lexem->variantOfId);
  $meanings = json_decode($jsonMeanings);

  if (validate($lexem, $variantOf, $variantIds, $meanings)) {
    // Case 1: Validation passed
    Meaning::saveTree($meanings, $lexem);
    $lexem->save();
    $lexem->updateVariants($variantIds);
    util_redirect("dexEdit.php?lexemId={$lexem->id}");
  } else {
    // Case 2: Validation failed
    SmartyWrap::assign('variantIds', $variantIds);
    SmartyWrap::assign('meanings', Meaning::convertTree($meanings));
  }
} else {
  // Case 3: First time loading this page
  SmartyWrap::assign('variantIds', $lexem->getVariantIds());
  SmartyWrap::assign('meanings', Meaning::loadTree($lexem->id));
}

$defs = Definition::loadByLexemId($lexem->id);
foreach ($defs as $def) {
  // $def->internalRep = AdminStringUtil::expandAbbreviations($def->internalRep, $def->sourceId);
}
$searchResults = SearchResult::mapDefinitionArray($defs);
$meaningTags = Model::factory('MeaningTag')->order_by_asc('value')->find_many();

SmartyWrap::assign('lexem', $lexem);
SmartyWrap::assign('meaningTags', $meaningTags);
SmartyWrap::assign('searchResults', $searchResults);
SmartyWrap::assign('pageTitle', "Editare lexem: {$lexem->formNoAccent}");
SmartyWrap::addCss('jqueryui', 'easyui', 'select2', 'struct', 'flex');
SmartyWrap::addJs('dex', 'jquery', 'easyui', 'jqueryui', 'select2', 'struct');
SmartyWrap::displayWithoutSkin('struct/dexEdit.ihtml');

/**************************************************************************/

function validate($lexem, $variantOf, $variantIds, $meanings) {
  if ($variantOf && !empty($meanings)) {
    FlashMessage::add("Acest lexem este o variantă a lui {$variantOf} și nu poate avea el însuși sensuri.");
  }
  if ($variantOf && !empty($variantIds)) {
    FlashMessage::add("Acest lexem este o variantă a lui {$variantOf} și nu poate avea el însuși variante.");
  }
  if ($variantOf && ($variantOf->id == $lexem->id)) {
    FlashMessage::add("Lexemul nu poate fi variantă a sa însăși.");
  }

  foreach ($variantIds as $variantId) {
    $variant = Lexem::get_by_id($variantId);
    if ($variant->id == $lexem->id) {
      FlashMessage::add('Un lexem nu poate fi variantă a lui însuși.');
    }
    if ($variant->variantOfId && $variant->variantOfId != $lexem->id) {
      $other = Lexem::get_by_id($variant->variantOfId);
      FlashMessage::add("\"{$variant}\" este deja marcat ca variantă a lui \"{$other}\".");
    }
    $variantVariantCount = Model::factory('Lexem')->where('variantOfId', $variant->id)->count();
    if ($variantVariantCount) {
      FlashMessage::add("\"{$variant}\" are deja propriile lui variante.");
    }
    $variantMeaningCount = Model::factory('Meaning')->where('lexemId', $variant->id)->count();
    if ($variantMeaningCount) {
      FlashMessage::add("\"{$variant}\" are deja propriile lui sensuri.");
    }
  }

  return FlashMessage::getMessage() == null;
}

?>
