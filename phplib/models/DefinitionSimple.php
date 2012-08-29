<?php

class DefinitionSimple extends BaseObject {
  public static $_table = 'DefinitionSimple';
  
  public function getDisplayValue()
  {
    return ucfirst($this->definition);
  }
}

?>
