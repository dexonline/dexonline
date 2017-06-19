<?php

class DefinitionVersion extends BaseObject {
  public static $_table = 'DefinitionVersion';

  const ACTION_UPDATE = 0;
  const ACTION_DELETE = 1;

  static $ACTION_NAMES = [
    self::ACTION_UPDATE => 'modificare',
    self::ACTION_DELETE => 'ștergere',
  ];

  function getStatusName() {
    return Definition::$STATUS_NAMES[$this->status];
  }

  static function current($def) {
    $dv = Model::factory('DefinitionVersion')->create();
    $dv->definitionId = $def->id;
    $dv->action = self::ACTION_UPDATE;
    $dv->sourceId = $def->sourceId;
    $dv->lexicon = $def->lexicon;
    $dv->internalRep = $def->internalRep;
    $dv->htmlRep = $def->htmlRep;
    $dv->status = $def->status;
    $dv->createDate = $def->modDate; // mind the difference
    $dv->modUserId = $def->modUserId;
    return $dv;
  }

  static function compare(&$old, &$new) {

    if (($old->sourceId == $new->sourceId) &&
        ($old->status == $new->status) &&
        ($old->lexicon == $new->lexicon) &&
        ($old->internalRep == $new->internalRep)) {
      return null;
    }

    $result = [
      'old' => $old,
      'new' => $new,
      'user' => User::get_by_id($new->modUserId),
      'oldSource' => Source::get_by_id($old->sourceId),
      'newSource' => Source::get_by_id($new->sourceId),
      'tags' => ObjectTag::getDefinitionVersionTags($old->id),
    ];

    if ($old->internalRep != $new->internalRep) {
      $result['diff'] = LDiff::htmlDiff($old->internalRep, $new->internalRep);
    }
    
    return $result;
  }

}
