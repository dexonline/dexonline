<?php

class SourceRole extends BaseObject {
  static $_table = 'SourceRole';

  function getName($count) {
    return ($count == 1) ? $this->nameSingular : $this->namePlural;
  }
}
