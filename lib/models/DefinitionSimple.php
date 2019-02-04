<?php

class DefinitionSimple extends BaseObject implements DatedObject {
  public static $_table = 'DefinitionSimple';
  
  function getDisplayValue()
  {
    return ucfirst($this->definition);
  }
}
