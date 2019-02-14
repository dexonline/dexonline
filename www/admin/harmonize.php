<?php

require_once '../../lib/Core.php';
User::mustHave(User::PRIV_ADMIN);

$deleteHarmonizeTagId = Request::get('deleteHarmonizeTagId');
$deleteHarmonizeModelId = Request::get('deleteHarmonizeModelId');
$applyHarmonizeTagId = Request::get('applyHarmonizeTagId');
$applyHarmonizeModelId = Request::get('applyHarmonizeModelId');
$saveHarmonizeTagButton = Request::has('saveHarmonizeTagButton');
$saveHarmonizeModelButton = Request::has('saveHarmonizeModelButton');

if ($deleteHarmonizeTagId) {
  HarmonizeTag::delete_all_by_id($deleteHarmonizeTagId);
  FlashMessage::add('Am șters regula.', 'success');
  Util::redirect('harmonize');
}

if ($deleteHarmonizeModelId) {
  HarmonizeModel::delete_all_by_id($deleteHarmonizeModelId);
  FlashMessage::add('Am șters regula.', 'success');
  Util::redirect('harmonize');
}

if ($applyHarmonizeTagId) {
  $ht = HarmonizeTag::get_by_id($applyHarmonizeTagId);
  $ht->apply();
  FlashMessage::add('Am aplicat regula.', 'success');
  Util::redirect('harmonize');
}

if ($applyHarmonizeModelId) {
  $hm = HarmonizeModel::get_by_id($applyHarmonizeModelId);
  $hm->apply();
  FlashMessage::add('Am aplicat regula.', 'success');
  Util::redirect('harmonize');
}

if ($saveHarmonizeTagButton) {
  $ht = Model::factory('HarmonizeTag')->create();
  $ht->modelType = Request::get('modelType');
  $ht->modelNumber = Request::get('modelNumber');
  $ht->tagId = Request::get('tagId');
  if ($ht->validate()) {
    $ht->save();
    FlashMessage::add('Am adăugat regula.', 'success');
    Util::redirect('harmonize');
  }
}

if ($saveHarmonizeModelButton) {
  $hm = Model::factory('HarmonizeModel')->create();
  $hm->modelType = Request::get('modelType');
  $hm->modelNumber = Request::get('modelNumber');
  $hm->tagId = Request::get('tagId');
  $hm->newModelType = Request::get('newModelType');
  $hm->newModelNumber = Request::get('newModelNumber');
  if ($hm->validate()) {
    $hm->save();
    FlashMessage::add('Am adăugat regula.', 'success');
    Util::redirect('harmonize');
  }
}

Smart::assign([
  'harmonizeTags' => HarmonizeTag::getAll(),
  'harmonizeModels' => HarmonizeModel::getAll(),
  'modelTypes' => ModelType::getAll(),
]);
Smart::addResources('admin', 'select2Dev', 'modelDropdown');
Smart::display('admin/harmonize.tpl');
