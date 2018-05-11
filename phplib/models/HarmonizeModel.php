<?php

class HarmonizeModel extends BaseObject implements DatedObject {
  static $_table = 'HarmonizeModel';

  function getTag() {
    return Tag::get_by_id($this->tagId);
  }

  static function getAll() {
    return Model::factory('HarmonizeModel')
      ->order_by_asc('modelType')
      ->order_by_expr('cast(modelNumber as unsigned)')
      ->find_many();
  }

  // Validates the rule. Sets flash errors if needed. Returns true on success.
  function validate() {
    if (!$this->tagId) {
      FlashMessage::add('Trebuie să alegeți o etichetă.');
    }

    if (($this->modelType == $this->newModelType) &&
        ($this->modelNumber == $this->newModelNumber)) {
      FlashMessage::add('Modelul nu poate fi același.');
    }

    return empty(FlashMessage::getMessages());
  }

}
