<?php
require_once("../../phplib/util.php"); 
util_assertModerator(PRIV_EDIT);
util_assertNotMirror();
DebugInfo::disable();

$prefix = util_getRequestParameter('prefix');
$submitButton = util_getRequestParameter('submitButton');

if ($submitButton) {
  // Collect all affected lexems beforehand
  $lexemMap = array();
  $errorMap = array();
  $deleteMap = array();
  foreach ($_REQUEST as $name => $value) {
    if ((StringUtil::startsWith($name, 'caps_') || StringUtil::startsWith($name, 'model_') || StringUtil::startsWith($name, 'comment_') ||
         StringUtil::startsWith($name, 'singular_') || StringUtil::startsWith($name, 'plural_') || StringUtil::startsWith($name, 'verifSp_') ||
         StringUtil::startsWith($name, 'delete_') || StringUtil::startsWith($name, 'deleteConfirm_'))
         && $value) {
      $parts = preg_split('/_/', $name);
      assert(count($parts) == 2);
      $lexemId = $parts[1];

      if (!array_key_exists($lexemId, $lexemMap)) {
        $lexemMap[$lexemId] = Lexem::get_by_id($lexemId);
      }
      $l = $lexemMap[$lexemId];

      switch ($parts[0]) {
      case 'caps':
        if (StringUtil::startsWith($l->form, "'")) {
          $l->form = "'" . AdminStringUtil::capitalize(mb_substr($l->form, 1));
        } else {
          $l->form = AdminStringUtil::capitalize($l->form);
        }
        $l->formNoAccent = str_replace("'", '', $l->form);
        $l->reverse = StringUtil::reverse($l->formNoAccent);
        break;
        
      case 'singular':
        $l->restriction = 'S';
        break;
        
      case 'plural':
        $l->restriction = 'P';
        break;
        
      case 'model':
        if ($value) {
          $m = Model::factory('FlexModel')->where_raw("concat(modelType, number) = '{$value}'")->find_one();
          if ($m) {
            $oldModelType = $l->modelType;
            $oldModelNumber = $l->modelNumber;
            $l->modelType = $m->modelType;
            $l->modelNumber = $m->number;
            $ifs = $l->generateParadigm();
            if (!is_array($ifs)) {
              FlashMessage::add("Lexemul '{$l->formNoAccent}' nu poate fi flexionat cu modelul '{$value}'");
              $errorMap[$l->id] = true;
              $l->modelType = $oldModelType;
              $l->modelNumber = $oldModelNumber;
            }
          } else {
            FlashMessage::add("Modelul '{$value}' nu există pentru lexemul '{$l->formNoAccent}'.");
            $errorMap[$l->id] = true;
          }
        }
        break;
        
      case 'verifSp':
        $l->verifSp = 1;
        break;
        
      case 'comment':
        if ($l->comment) {
          $l->comment = $value;
        }
        break;

      case 'delete':
      case 'deleteConfirm':
        $deleteMap[$l->id] = array_key_exists($l->id, $deleteMap) ? ($deleteMap[$l->id] + 1) : 1;
        break;
      }
    }
  }

  // Delete lexems
  foreach ($deleteMap as $lId => $value) {
    if ($value == 2) { // Checked and confirmed
      $l = Lexem::get_by_id($lId);
      $l->delete();
    }
  }

  // Now save the ones that can be saved and present errors for the others
  foreach ($lexemMap as $id => $l) {
    if (!array_key_exists($id, $errorMap) && !array_key_exists($id, $deleteMap)) {
      $l->save();
      $l->regenerateParadigm();
    }
  }
}

$deSource = Source::get_by_shortName('DE');
$lexems = Model::factory('Lexem')->distinct()->select('Lexem.*')
  ->join('LexemDefinitionMap', 'Lexem.id = lexemId')->join('Definition', 'definitionId = Definition.id')
  ->where('status', 0)->where('sourceId', $deSource->id)->where('isLoc', 0)->where_like('formNoAccent', "$prefix%")->where('verifSp', 0)
  ->where_not_equal('modelType', 'SP')
  ->order_by_asc('formNoAccent')->limit(100)->find_many();

foreach ($lexems as $l) {
  $l->restrP = (strpos($l->restriction, 'P') !== false);
  $l->restrS = (strpos($l->restriction, 'S') !== false);
}

RecentLink::createOrUpdate('Marcare substantive proprii');
smarty_assign('sectionTitle', 'Marcare substantive proprii');
smarty_assign('recentLinks', RecentLink::loadForUser());
smarty_assign('lexems', $lexems);
smarty_assign('prefix', $prefix);
smarty_displayAdminPage('admin/properNouns.ihtml');

?>
