<?php

class ParticipleModel extends BaseObject {
  public static $_table = 'ParticipleModel';

  public static function loadByVerbModel($verbModel) {
    return ParticipleModel::get_by_verbModel(addslashes($verbModel));
  }
}

?>
