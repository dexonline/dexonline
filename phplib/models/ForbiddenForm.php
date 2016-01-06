<?php

class ForbiddenForm extends BaseObject {
  public static $_table = 'ForbiddenForm';

  static function create($inflectedForm) {
    $ff = Model::factory('ForbiddenForm')->create();
    $ff->lexemModelId = $inflectedForm->lexemModelId;
    $ff->inflectionId = $inflectedForm->inflectionId;
    $ff->variant = $inflectedForm->variant;
    return $ff;
  }
}
