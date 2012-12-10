<?php

abstract class DefinitionParser {
  abstract function parse($def);
}

class Structure {
  static function parse($def) {
    switch ($def->sourceId) {
    case 1:
      require_once('structure/dex98.php');
      $p = new Dex98DefinitionParser();
    }
    return $p->parse($def);
  }
}

?>
