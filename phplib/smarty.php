<?php

require_once(pref_getSmartyClass());

// Create an instance of Smarty, assign some default parameters for the
// header and footer and return it.
function smarty_init() {
  $smarty = new Smarty();
  $smarty->template_dir = util_getRootPath() . 'templates';
  $smarty->compile_dir = util_getRootPath() . 'templates_c';
  $smarty->assign('wwwRoot', util_getWwwRoot());
  $smarty->assign('imgRoot', util_getImgRoot());
  $smarty->assign('sources', Model::factory('Source')->order_by_desc('isOfficial')->order_by_asc('displayOrder')->find_many());
  $smarty->assign('sUser', session_getUser());
  $smarty->assign('is_mirror', pref_isMirror());
  $smarty->assign('nick', session_getUserNick());
  $smarty->assign('contact_email', pref_getContactEmail());
  $smarty->assign('hostedBy', pref_getHostedBy());
  $smarty->assign('currentYear', date("Y"));
  $smarty->assign('bannerType', pref_getServerPreference('bannerType'));
  $smarty->assign('developmentMode', pref_getServerPreference('developmentMode'));
  $smarty->assign('isMobile', util_isMobile());
  $smarty->assign('suggestNoBanner', util_suggestNoBanner());
  smarty_registerFunction($smarty, 'getDebugInfo', 'smarty_function_getDebugInfo');
  $smarty->assign('GLOBALS', $GLOBALS);
  $GLOBALS['smarty_theSmarty'] = $smarty;
  $GLOBALS['smarty_cssFiles'] = array();
  $GLOBALS['smarty_jsFiles'] = array();
}

function smarty_isInitialized() {
  return array_key_exists('smarty_theSmarty', $GLOBALS);
}

function smarty_display() {
  print smarty_fetchSkin();
}

function smarty_fetchSkin() {
  $skin = session_getSkin();
  smarty_addCss($skin);
  smarty_addJs('jquery', 'dex');

  // Set some skin variables based on the skin preferences in the config file.
  // Also assign some skin-specific variables so we don't compute them unless we need them
  $skinVariables = session_getSkinPreferences($skin);
  switch ($skin) {
  case 'zepu':
    $skinVariables['afterSearchBoxBanner'] = true;
    break;
  case 'polar':
    $wordCount = Definition::getWordCount();
    $wordCountRough = $wordCount - ($wordCount % 10000);
    smarty_assign('words_total', util_formatNumber($wordCount, 0));
    smarty_assign('words_rough', util_formatNumber($wordCountRough, 0));
    smarty_assign('words_last_month', util_formatNumber(Definition::getWordCountLastMonth(), 0));
    break;
  case 'mobile':
    smarty_assign('words_total', util_formatNumber(Definition::getWordCount(), 0));
    smarty_assign('words_last_month', util_formatNumber(Definition::getWordCountLastMonth(), 0));
    smarty_addJs('mobile');
    break;
  }
  smarty_assign('skinVariables', $skinVariables);

  smarty_register_outputfilters();
  return smarty_fetch("$skin/pageLayout.ihtml");
}

function smarty_displayCommonPageWithSkin($templateName) {
  print smarty_fetchCommonPageWithSkin($templateName);
}

function smarty_fetchCommonPageWithSkin($templateName) {
  smarty_assign('contentTemplateName', "common/$templateName");  
  return smarty_fetchSkin();
}

function smarty_displayPageWithSkin($templateName) {
  $skin = session_getSkin();
  smarty_assign('contentTemplateName', "$skin/$templateName");
  smarty_display();
}

function smarty_displayWithoutSkin($templateName) {
  smarty_register_outputfilters();
  print smarty_fetch($templateName);
}

function smarty_displayAdminPage($templateName) {
  smarty_assign('templateName', $templateName);
  smarty_addCss('flex');
  smarty_addJs('dex', 'flex');
  print smarty_fetch('admin/pageLayout.ihtml');
}

function smarty_fetch($templateName) {
  ksort($GLOBALS['smarty_cssFiles']);
  ksort($GLOBALS['smarty_jsFiles']);
  smarty_assign('cssFiles', $GLOBALS['smarty_cssFiles']);
  smarty_assign('jsFiles', $GLOBALS['smarty_jsFiles']);
  return $GLOBALS['smarty_theSmarty']->fetch($templateName);
}

function smarty_assign($variable, $value) {
  $GLOBALS['smarty_theSmarty']->assign($variable, $value);
}

function smarty_filter_display_st_cedilla_below($tpl_output, &$smarty) {
  $tpl_output = StringUtil::replace_st($tpl_output);
  return $tpl_output;
}

function smarty_filter_display_old_orthography($tpl_output, &$smarty) {
  $tpl_output = StringUtil::replace_ai($tpl_output);
  return $tpl_output;
}

function smarty_register_outputfilters() {
  if (session_user_prefers('CEDILLA_BELOW')) {
    smarty_registerOutputFilter($GLOBALS['smarty_theSmarty'], 'smarty_filter_display_st_cedilla_below');
  }
  if (session_user_prefers('OLD_ORTHOGRAPHY')) {
    smarty_registerOutputFilter($GLOBALS['smarty_theSmarty'], 'smarty_filter_display_old_orthography');
  }
}

function smarty_registerOutputFilter($smarty, $functionName) {
  if (method_exists($smarty, 'registerFilter')) {
    // Smarty v3 syntax
    $smarty->registerFilter('output', $functionName);
  } else {
    $smarty->register_outputfilter($functionName);
  }
}

function smarty_registerFunction($smarty, $smartyTagName, $functionName) {
  if (method_exists($smarty, 'registerPlugin')) {
    // Smarty v3 syntax
    $smarty->registerPlugin('function', $smartyTagName, $functionName);
  } else {
    $smarty->register_function($smartyTagName, $functionName);
  }
}

function smarty_function_getDebugInfo($params, &$smarty) {
  return DebugInfo::getDebugInfo();
}

function smarty_addCss(/* Variable-length argument list */) {
  // Note the priorities. This allows files to be added in any order, regardless of dependencies
  foreach (func_get_args() as $id) {
    switch($id) {
    case 'jquery_smoothness':  $GLOBALS['smarty_cssFiles'][1] = 'jquery-ui-1.8.5.custom.css'; break;
    case 'jqgrid':
      $GLOBALS['smarty_cssFiles'][2] = 'ui.jqgrid.css?v=1';
      $GLOBALS['smarty_cssFiles'][3] = 'jquery-ui-1.8.5.custom.css?v=1';
      break;
    case 'autocomplete':       $GLOBALS['smarty_cssFiles'][4] = 'jquery.autocomplete.css?v=1'; break;
    case 'elfinder':           $GLOBALS['smarty_cssFiles'][5] = 'elfinder.css'; break;
    case 'zepu':               $GLOBALS['smarty_cssFiles'][6] = 'zepu.css?v=43'; break;
    case 'polar':              $GLOBALS['smarty_cssFiles'][7] = 'polar.css?v=29'; break;
    case 'mobile':             $GLOBALS['smarty_cssFiles'][8] = 'mobile.css?v=14'; break;
    case 'flex':               $GLOBALS['smarty_cssFiles'][9] = 'flex.css?v=7'; break;
    case 'paradigm':           $GLOBALS['smarty_cssFiles'][10] = 'paradigm.css?v=1'; break;
    case 'hangman':            $GLOBALS['smarty_cssFiles'][11] = 'hangman.css?v=2'; break;
    case 'mill':               $GLOBALS['smarty_cssFiles'][12] = 'mill.css?v=1'; break;
    default:
      FlashMessage::add("Cannot load CSS file {$id}");
      util_redirect(util_getWwwRoot());
    }
  }
}

function smarty_addJs(/* Variable-length argument list */) {
  // Note the priorities. This allows files to be added in any order, regardless of dependencies
  foreach (func_get_args() as $id) {
    switch($id) {
    case 'jquery':           $GLOBALS['smarty_jsFiles'][1] = 'jquery-1.7.1.min.js'; break; 
    case 'jqueryui':         $GLOBALS['smarty_jsFiles'][2] = 'jquery-ui-1.8.17.custom.min.js'; break;
    case 'jqgrid':
      $GLOBALS['smarty_jsFiles'][3] = 'grid.locale-en.js?v=1';
      $GLOBALS['smarty_jsFiles'][4] = 'jquery.datepicker.pack.js?v=1';
      $GLOBALS['smarty_jsFiles'][5] = 'jquery.jqGrid.min.js?v=1';
      $GLOBALS['smarty_jsFiles'][6] = 'jqgrid.init.js?v=3';
      break;
    case 'jqnotice':         $GLOBALS['smarty_jsFiles'][7] = 'jquery.notice.js'; break;
    case 'jqTableDnd':       $GLOBALS['smarty_jsFiles'][8] = 'jquery.tablednd_0_5.js?v=1'; break;
    case 'tablesorter':      $GLOBALS['smarty_jsFiles'][9] = 'jquery.tablesorter.min.js'; break;
    case 'pager':            $GLOBALS['smarty_jsFiles'][10] = 'jquery.tablesorter.pager.js'; break;
    case 'autocomplete':     $GLOBALS['smarty_jsFiles'][11] = 'jquery.autocomplete.pack.js'; break;
    case 'elfinder':         $GLOBALS['smarty_jsFiles'][12] = 'elfinder.min.js'; break; 
    case 'dex':              $GLOBALS['smarty_jsFiles'][13] = 'dex.js?v=21'; break;
    case 'flex':             $GLOBALS['smarty_jsFiles'][14] = 'flex.js?v=2'; break;
    case 'mobile':           $GLOBALS['smarty_jsFiles'][15] = 'mobile.js?v=2'; break;
    case 'hangman':          $GLOBALS['smarty_jsFiles'][16] = 'hangman.js?v=3'; break;
    case 'mill':             $GLOBALS['smarty_jsFiles'][17] = 'mill.js?v=2'; break;
    default:
      FlashMessage::add("Cannot load JS script {$id}");
      util_redirect(util_getWwwRoot());
    }
  }
}

?>
