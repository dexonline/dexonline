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
      $lm = $l->getFirstLexemModel();

      switch ($parts[0]) {
      case 'caps':
        if (StringUtil::startsWith($l->form, "'")) {
          $l->form = "'" . AdminStringUtil::capitalize(mb_substr($l->form, 1));
        } else {
          $l->form = AdminStringUtil::capitalize($l->form);
        }
        $l->formNoAccent = str_replace("'", '', $l->form);
        break;
        
      case 'singular':
        $lm->restriction = 'S';
        break;
        
      case 'plural':
        $lm->restriction = 'P';
        break;
        
      case 'model':
        if ($value) {
          $m = Model::factory('FlexModel')->where_raw("concat(modelType, number) = '{$value}'")->find_one();
          if ($m) {
            $oldModelType = $lm->modelType;
            $oldModelNumber = $lm->modelNumber;
            $lm->modelType = $m->modelType;
            $lm->modelNumber = $m->number;
            $ifs = $lm->generateInflectedForms();
            if (!is_array($ifs)) {
              FlashMessage::add("Lexemul '{$l->formNoAccent}' nu poate fi flexionat cu modelul '{$value}'");
              $errorMap[$l->id] = true;
              $lm->modelType = $oldModelType;
              $lm->modelNumber = $oldModelNumber;
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
      $lm = $l->getFirstLexemModel();
      $lm->save();
      $lm->regenerateParadigm();
    }
  }
}

$deSource = Source::get_by_shortName('DE');
$lexems = Model::factory('Lexem')
  ->table_alias('l')
  ->select('l.*')
  ->distinct()
  ->join('LexemModel', 'l.id = lm.lexemId', 'lm')
  ->join('LexemDefinitionMap', 'l.id = ldm.lexemId', 'ldm')
  ->join('Definition', 'ldm.definitionId = d.id', 'd')
  ->where('d.status', ST_ACTIVE)
  ->where('d.sourceId', $deSource->id)
  ->where('lm.isLoc', 0)
  ->where_like('l.formNoAccent', "$prefix%")
  ->where('l.verifSp', 0)
  ->where_not_equal('lm.modelType', 'SP')
  ->order_by_asc('l.formNoAccent')
  ->limit(100)
  ->find_many();

foreach ($lexems as $l) {
  $l->restrP = (strpos($l->restriction, 'P') !== false);
  $l->restrS = (strpos($l->restriction, 'S') !== false);
}

RecentLink::createOrUpdate('Marcare substantive proprii');
SmartyWrap::assign('recentLinks', RecentLink::loadForUser());
SmartyWrap::assign('lexems', $lexems);
SmartyWrap::assign('prefix', $prefix);
SmartyWrap::displayAdminPage('admin/properNouns.tpl');

?>
