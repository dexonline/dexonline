<?php

class DefinitionVersion extends BaseObject {
  public static $_table = 'DefinitionVersion';

  const ACTION_UPDATE = 0;
  const ACTION_DELETE = 1;

  public static $ACTION_NAMES = [
    self::ACTION_UPDATE => 'modificare',
    self::ACTION_DELETE => 'ștergere',
  ];
