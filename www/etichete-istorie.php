<?php
require_once '../phplib/Core.php';

User::mustHave(User::PRIV_EDIT);

$id = Request::get('id');
$saveButton = Request::has('saveButton');

$dv = DefinitionVersion::get_by_id($id);
if (!$dv) {
  FlashMessage::add("Nu există nicio înregistrare istorică cu ID-ul {$id}.");
  Util::redirectToHome();
}

$def = Definition::get_by_id($dv->definitionId);

if ($saveButton) {
  $tagIds = Request::getArray('tagIds');
  $applyToDefinition = Request::has('applyToDefinition');

  ObjectTag::wipeAndRecreate($dv->id, ObjectTag::TYPE_DEFINITION_VERSION, $tagIds);
  if ($applyToDefinition) {
    foreach ($tagIds as $tagId) {
      ObjectTag::associate(ObjectTag::TYPE_DEFINITION, $def->id, $tagId);
    }
  }
  Log::notice("Saved new tags on DefinitionVersion {$dv->id}");
  FlashMessage::add('Am salvat etichetele.', 'success');
  Util::redirect("etichete-istorie?id={$dv->id}");
}

$next = Model::factory('DefinitionVersion')
  ->where('definitionId', $dv->definitionId)
  ->where_gt('id', $dv->id)
  ->order_by_asc('id')
  ->find_one();
if (!$next) {
  $next = DefinitionVersion::current($def);
}

$change = DefinitionVersion::compare($dv, $next);

SmartyWrap::assign('def', $def);
SmartyWrap::assign('dv', $dv);
SmartyWrap::assign('change', $change);
SmartyWrap::addCss('diff');
SmartyWrap::addJs('select2Dev', 'diff');
SmartyWrap::display('etichete-istorie.tpl');
