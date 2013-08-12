<?php

class BaseObject extends Model {
  /**
   * Accept calls like User::get_by_email($email) and User::get_all_by_email($email)
   **/
  static function __callStatic($name, $arguments) {
    if (substr($name, 0, 7) == 'get_by_' && count($arguments) == 1) {
      $field = substr($name, 7);
      return Model::factory(get_called_class())->where($field, $arguments[0])->find_one();
    }
    if (substr($name, 0, 11) == 'get_all_by_' && count($arguments) == 1) {
      $field = substr($name, 11);
      return Model::factory(get_called_class())->where($field, $arguments[0])->find_many();
    }
    die("BaseObject::__callStatic() cannot handle method '$name'");
  }

  /* Loads a collection of objects with the given ids, preserving the order. */
  static function loadByIds($ids) {
    $results = array();
    foreach ($ids as $id) {
      $results[] = Model::factory(get_called_class())->where('id', $id)->find_one();
    }
    return $results;
  }

  function save() {
    /* Auto-save the createDate and modDate fields if the model has them */
    if ($this instanceof DatedObject) {
      $this->modDate = time();
      if (!$this->createDate) {
        $this->createDate = $this->modDate;
      }
    }
    return parent::save();
  }
}

?>
