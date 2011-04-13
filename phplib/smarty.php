<?php

require_once(pref_getSmartyClass());

// Create an instance of Smarty, assign some default parameters for the
// header and footer and return it.
function smarty_init() {
  $smarty = new Smarty;
  $smarty->template_dir = util_getRootPath() . 'templates';
  $smarty->compile_dir = util_getRootPath() . 'templates_c';
  $smarty->assign('wwwRoot', util_getWwwRoot());
  $smarty->assign('cssRoot', util_getCssRoot());
  $smarty->assign('imgRoot', util_getImgRoot());
  $smarty->assign('sources', db_find(new Source(), '1 order by isOfficial desc, displayOrder'));
  $smarty->assign('sUser', session_getUser());
  $smarty->assign('is_mirror', pref_isMirror());
  $smarty->assign('nick', session_getUserNick());
  $wordCount = Definition::getWordCount();
  $wordCountRough = $wordCount - ($wordCount % 10000);
  $smarty->assign('words_total', util_formatNumber($wordCount, 0));
  $smarty->assign('words_rough', util_formatNumber($wordCountRough, 0));
  $smarty->assign('words_last_month', util_formatNumber(Definition::getWordCountLastMonth(), 0));
  $smarty->assign('contact_email', pref_getContactEmail());
  $smarty->assign('debug', session_isDebug());
  $smarty->assign('hostedBy', pref_getHostedBy());
  $smarty->assign('currentYear', date("Y"));
  $smarty->assign('bannerType', pref_getServerPreference('bannerType'));
  $GLOBALS['smarty_theSmarty'] = $smarty;
}

function smarty_isInitialized() {
  return array_key_exists('smarty_theSmarty', $GLOBALS);
}

function smarty_display() {
  $skin = session_getSkin();

  // Set some skin variables based on the skin preferences in the config file.
  $skinVariables = session_getSkinPreferences($skin);
  switch ($skin) {
  case 'zepu':
    if (rand(0, 9999) < $skinVariables['topBannerProbability'] * 10000) {
      $skinVariables['topBanner'] = true;
      $skinVariables['afterSearchBoxBanner'] = false;
    } else {
      $skinVariables['topBanner'] = false;
      $skinVariables['afterSearchBoxBanner'] = true;
    }
    break;
  case 'polar':
    // No tweaks here
    break;
  }
  smarty_assign('skinVariables', $skinVariables);

  smarty_register_outputfilters();
  $GLOBALS['smarty_theSmarty']->display("$skin/pageLayout.ihtml");
}

function smarty_displayCommonPageWithSkin($templateName) {
  smarty_assign('contentTemplateName', "common/$templateName");
  smarty_display();
}

function smarty_displayPageWithSkin($templateName) {
  $skin = session_getSkin();
  smarty_assign('contentTemplateName', "$skin/$templateName");
  smarty_display();
}

function smarty_displayWithoutSkin($templateName) {
  smarty_register_outputfilters();
  $GLOBALS['smarty_theSmarty']->display($templateName);
}

function smarty_fetch($templateName) {
  return $GLOBALS['smarty_theSmarty']->fetch($templateName);
}

function smarty_assign($variable, $value) {
  $GLOBALS['smarty_theSmarty']->assign($variable, $value);
}

function smarty_filter_display_st_cedilla_below($tpl_output, &$smarty) {
  $tpl_output = text_replace_st($tpl_output);
  return $tpl_output;
}

function smarty_filter_display_old_orthography($tpl_output, &$smarty) {
  $tpl_output = text_replace_ai($tpl_output);
  return $tpl_output;
}

function smarty_register_outputfilters() {
  if (session_user_prefers('CEDILLA_BELOW')) {
    $GLOBALS['smarty_theSmarty']->register_outputfilter('smarty_filter_display_st_cedilla_below');
  }
  if (session_user_prefers('OLD_ORTHOGRAPHY')) {
    $GLOBALS['smarty_theSmarty']->register_outputfilter('smarty_filter_display_old_orthography');
  }
}
?>
