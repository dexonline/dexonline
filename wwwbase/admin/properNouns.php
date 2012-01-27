<?php
require_once("../../phplib/util.php"); 
util_assertModerator(PRIV_EDIT);
util_assertNotMirror();
DebugInfo::disable();

$prefix = util_getRequestParameter('prefix');
$submitButton = util_getRequestParameter('submitButton');

if ($submitButton) {
  foreach ($_REQUEST as $name => $value) {
    if ((StringUtil::startsWith($name, 'caps_') || StringUtil::startsWith($name, 'model_') || StringUtil::startsWith($name, 'comment_')) &&
        $value) {
      $parts = preg_split('/_/', $name);
      assert(count($parts) == 2);
      $l = Lexem::get_by_id($parts[1]);

      switch ($parts[0]) {
      case 'caps':
        if (StringUtil::startsWith($l->form, "'")) {
          $l->form = "'" . AdminStringUtil::capitalize(mb_substr($l->form, 1));
        } else {
          $l->form = AdminStringUtil::capitalize($l->form);
        }
        $l->formNoAccent = str_replace("'", '', $l->form);
        $l->reverse = StringUtil::reverse($l->formNoAccent);
        $l->regenerateParadigm();
        break;
      case 'model':
        if ($value == 'i3') {
          $l->modelType = 'I';
          $l->modelNumber = '3';
        } else if ($value == 'i4') {
          $l->modelType = 'I';
          $l->modelNumber = '4';
        }
        $l->regenerateParadigm();
        break;
      case 'comment':
        $l->comment = $value;
        break;
      }
      $l->save();
    }
  }
}

$deSource = Source::get_by_shortName('DE');
// Do not select lexems which are already capitalized AND have a model number different from I1 and T1.
// The where_raw condition for this is complicated.
$lexems = Model::factory('Lexem')->distinct()->select('Lexem.*')
  ->join('LexemDefinitionMap', 'Lexem.id = lexemId')->join('Definition', 'definitionId = Definition.id')
  ->where('status', 0)->where('sourceId', $deSource->id)->where_like('formNoAccent', "$prefix%")
  ->where_raw('((binary upper(left(formNoAccent, 1)) != left(formNoAccent, 1)) or (modelType = "I" and modelNumber = "1") or (modelType = "T" and modelNumber = "1"))')
  ->order_by_asc('formNoAccent')->limit(1000)->find_many();

RecentLink::createOrUpdate('Marcare substantive proprii');
smarty_assign('sectionTitle', 'Marcare substantive proprii');
smarty_assign('recentLinks', RecentLink::loadForUser());
smarty_assign('lexems', $lexems);
smarty_assign('prefix', $prefix);
smarty_displayWithoutSkin('admin/properNouns.ihtml');

?>
