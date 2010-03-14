<?php
require_once("../phplib/util.php");

$cuv = util_getRequestParameter('cuv');
$lexemId = util_getRequestParameter('lexemId');
$ajax = util_getRequestParameter('ajax');

if ($cuv) {
  $cuv = text_cleanupQuery($cuv);
}

$searchType = SEARCH_INFLECTED;
$hasDiacritics = session_user_prefers('FORCE_DIACRITICS');

if ($cuv) {
  smarty_assign('cuv', $cuv);
  smarty_assign('page_title', "Paradigmă: {$cuv} | DEX online");
}

// LexemId search
if ($lexemId) {
  $searchType = SEARCH_LEXEM_ID;
  smarty_assign('lexemId', $lexemId);
  if (!text_validateAlphabet($lexemId, '0123456789')) {
    $lexemId = '';
  }
  $lexem = Lexem::get("id = {$lexemId}");
  if ($lexem) {
    $lexems = array($lexem);
    smarty_assign('cuv', $lexem->formNoAccent);
    smarty_assign('page_title', "Paradigmă: {$lexem->formNoAccent} | DEX online");
  } else {
    $lexems = array();
    smarty_assign('page_title', "Eroare | DEX online");
  }
}

// Normal search
if ($searchType == SEARCH_INFLECTED) {
  $lexems = Lexem::searchInflectedForms($cuv, $hasDiacritics);
  if (count($lexems) == 0) {
    $cuv_old = text_tryOldOrthography($cuv);
    $lexems = Lexem::searchInflectedForms($cuv_old, $hasDiacritics);
  }
}

// Maps lexems to arrays of inflected forms (some lexems may lack inflections)
// Also compute the text of the link to the paradigm div,
// which can be 'conjugări', 'declinări' or both
if (!empty($lexems)) {
  $ifMaps = array();
  $conjugations = false;
  $declensions = false;
  foreach ($lexems as $l) {
    $ifMaps[] = InflectedForm::loadByLexemIdMapByInflectionId($l->id);
    if ($l->modelType == 'V' || $l->modelType == 'VT') {
	  $conjugations = true;
    } else {
	  $declensions = true;
    }
  }

$declensionText = $conjugations ? ($declensions ? 'conjugări / declinări' : 'conjugări') : 'declinări';
smarty_assign('lexems', $lexems);
smarty_assign('ifMaps', $ifMaps);
smarty_assign('showParadigm', true);
smarty_assign('onlyParadigm', !$ajax);
smarty_assign('declensionText', $declensionText);
}

if ($ajax) {
  smarty_displayWithoutSkin('common/bits/multiParadigm.ihtml');
}
else {
  smarty_displayCommonPageWithSkin('search.ihtml');
}
?>
