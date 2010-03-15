<?php
require_once("../phplib/util.php");

define('TYPE_SHOW_ONLY_VERBS', 'conjugare');
define('TYPE_SHOW_NO_VERBS', 'declinare');

$cuv = util_getRequestParameter('cuv');
$lexemId = util_getRequestParameter('lexemId');
$ajax = util_getRequestParameter('ajax');
$type = util_getRequestParameter('type');

if ($cuv) {
  $cuv = text_cleanupQuery($cuv);
}

$searchType = SEARCH_INFLECTED;
$hasDiacritics = session_user_prefers('FORCE_DIACRITICS');

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
	$cuv = $lexem->formNoAccent;
  } else {
    $lexems = array();
	$cuv = NULL;
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
  $filtered_lexems = array();
  foreach ($lexems as $l) {
	if (TYPE_SHOW_ONLY_VERBS == $type) {
	  if ($l->modelType == 'V' || $l->modelType == 'VT') {
		$filtered_lexems[] = $l;
	    $conjugations = true;
        $ifMaps[] = InflectedForm::loadByLexemIdMapByInflectionId($l->id);
	  }
	}
	elseif (TYPE_SHOW_NO_VERBS == $type) {
	  if ($l->modelType != 'V' && $l->modelType != 'VT') {
		$filtered_lexems[] = $l;
	    $declensions = true;
        $ifMaps[] = InflectedForm::loadByLexemIdMapByInflectionId($l->id);
	  }
	}
	else {
	  $filtered_lexems[] = $l;
      $ifMaps[] = InflectedForm::loadByLexemIdMapByInflectionId($l->id);
      if ($l->modelType == 'V' || $l->modelType == 'VT') {
	    $conjugations = true;
      } else {
	    $declensions = true;
      }
	}
  }

  if (empty($filtered_lexems)) {
    session_setFlash("Niciun rezultat pentru {$cuv}.");
    smarty_assign('page_title', "Eroare | DEX online");
  }

  $declensionText = $conjugations ? ($declensions ? 'Conjugare / Declinare' : 'Conjugare') : ($declensions ? 'Declinare' : '');

  if ($cuv && !empty($filtered_lexems)) {
    smarty_assign('cuv', $cuv);
    smarty_assign('page_title', "{$declensionText}: {$cuv} | DEX online");
    smarty_assign('declensionText', "{$declensionText}: {$cuv}");
  }

  smarty_assign('lexems', $filtered_lexems);
  smarty_assign('ifMaps', $ifMaps);
  smarty_assign('showParadigm', true);
  smarty_assign('onlyParadigm', !$ajax);
}
else {
  session_setFlash("Niciun rezultat pentru {$cuv}.");
  smarty_assign('page_title', "Eroare | DEX online");
}

if ($ajax) {
  smarty_displayWithoutSkin('common/bits/multiParadigm.ihtml');
}
else {
  smarty_displayCommonPageWithSkin('search.ihtml');
}
?>
