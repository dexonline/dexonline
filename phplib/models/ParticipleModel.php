<?php

class ParticipleModel extends BaseObject {
  public static $_table = 'ParticipleModel';

  public static function loadByVerbModel($verbModel) {
    return ParticipleModel::get_by_verbModel(addslashes($verbModel));
  }

  public static function loadForModel($m) {
    $pm = ($m->modelType == 'V')
        ? self::loadByVerbModel($m->number)
        : null;
    return $pm;
  }
}

?>
