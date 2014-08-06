<?php
require_once("../phplib/util.php");

$value = util_getRequestParameter('value');
$deleteId = util_getRequestParameter('deleteId');
$submitButton = util_getRequestParameter('submitButton');

if ($deleteId) {
  $mt = MeaningTag::get_by_id($deleteId);
  $mtms = MeaningTagMap::get_all_by_meaningTagId($mt->id);
  if (count($mtms)) {
    FlashMessage::add("Nu pot șterge eticheta «{$mt->value}», deoarece unele sensuri o folosesc.", 'error');
  } else {
    $mt->delete();
    FlashMessage::add("Am șters eticheta «{$mt->value}».", 'info');
  }
  util_redirect('etichete-sensuri');
}

if ($submitButton) {
  util_assertModerator(PRIV_ADMIN);
  $values = explode(',', $value);
  foreach ($values as $value) {
    $value = mb_strtolower(trim($value));
    if ($value && !MeaningTag::get_by_value($value)) {
      $mt = Model::factory('MeaningTag')->create();
      $mt->value = $value;
      $mt->save();
    }
  }
  FlashMessage::add('Etichetele au fost salvate.', 'info');
  util_redirect('etichete-sensuri');
}

$meaningTags = Model::factory('MeaningTag')->order_by_asc('value')->find_many();

SmartyWrap::assign('meaningTags', $meaningTags);
SmartyWrap::assign('page_title', 'Etichete pentru sensuri');
SmartyWrap::display('etichete-sensuri.ihtml');

?>
