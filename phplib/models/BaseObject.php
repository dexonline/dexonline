<?php

class BaseObject extends Model {
  const ACTION_SELECT = 1;
  const ACTION_SELECT_ALL = 2;
  const ACTION_DELETE_ALL = 3;

  function __call($name, $arguments) {
    // Work around some strange behavior in PHP:
    // Class Foo {
    //   function bar() {
    //     $x = Foo::get_by_id(17);
    //     ... here get_by_id calls __call, not __callStatic
    //   }
    // }
    if (preg_match('/^get[A-Z]/', $name)) {
      return $this->associationHandler($name, $arguments);
    } else {
      return $this->callHandler($name, $arguments);
    }
  }

  static function __callStatic($name, $arguments) {
    return self::callHandler($name, $arguments);
  }

  // Handle calls like $foo->getBars() and $foo->getBarIds() for associated objects
  // Optional arguments:
  //   - $payLoad: array($field => $value, ...). If set, bypasses the cache.
  // Example: $entry->getLexemes(['main' => true])
  function associationHandler($name, $arguments) {
    $cleft = get_called_class(); // capitalized
    $left = strtolower($cleft);
    $cright = substr($name, 3);
    $cacheKey = lcfirst($cright);

    // 1. sanity checks
    if (isset(Association::PLURALS[$cright])) {
      // $foo->getBars()
      $cright = Association::PLURALS[$cright];
      $loadIds = false; // load whole objects
    } else if (Str::endsWith($cright, 'Ids')) {
      $cright = substr($cright, 0, -3);
      $loadIds = true;
    } else {
      self::_die('Cannot parse getter name', $name, $arguments);
    }

    if (!class_exists($cright)) {
      self::_die("Class {$cright} does not exist", $name, $arguments);      
    }

    // get the association table name
    if (class_exists($cleft . $cright)) {
      $assoc = $cleft . $cright;
    } else if (class_exists($cright . $cleft)) {
      $assoc = $cright . $cleft;
    } else {
      self::_die("No association between {$cleft} and {$cright}", $name, $arguments);
    }

    // 2. cache lookup when there are no arguments
    if (empty($arguments) && isset($this->$cacheKey)) {
      return $this->$cacheKey;
    }

    // 3. backend query
    $right = strtolower($cright);

    $query = Model::factory($cright)
           ->table_alias('obj')
           ->join($assoc, ['obj.id', '=', "{$assoc}.{$right}Id"])
           ->where("{$assoc}.{$left}Id", $this->id)
           ->order_by_asc("{$assoc}.{$right}Rank")
           ->order_by_asc("{$assoc}.id");

    if (!empty($arguments)) {
      // payload
      foreach ($arguments[0] as $key => $value) {
        $query = $query->where("{$assoc}.{$key}", $value);
      }
    }

    if ($loadIds) {
      $results = $query->select('obj.id')->find_many();
      $results = Util::objectProperty($results, 'id');
    } else {
      $results = $query->select('obj.*')->find_many();
    }

    if (empty($arguments)) {
      $this->$cacheKey = $results;
    }
    return $results;
  }

  // Handle calls like User::get_by_email($email) and User::get_all_by_email($email)
  static function callHandler($name, $arguments) {
    if (substr($name, 0, 7) == 'get_by_') {
      return self::action(substr($name, 7), $arguments, self::ACTION_SELECT);
    } else if (substr($name, 0, 11) == 'get_all_by_') {
      return self::action(substr($name, 11), $arguments, self::ACTION_SELECT_ALL);
    } else if (substr($name, 0, 14) == 'delete_all_by_') {
      self::action(substr($name, 14), $arguments, self::ACTION_DELETE_ALL);
    } else {
      self::_die('cannot handle method', $name, $arguments);
    }
  }

  private static function action($fieldString, $arguments, $action) {
    $fields = explode('_', $fieldString);
    if (count($fields) != count($arguments)) {
      self::_die('incorrect number of arguments', $action, $arguments);
    }
    $clause = Model::factory(get_called_class());
    foreach ($fields as $i => $field) {
      $clause = $clause->where($field, $arguments[$i]);
    }

    switch ($action) {
      case self::ACTION_SELECT: return $clause->find_one();
      case self::ACTION_SELECT_ALL: return $clause->find_many();
      case self::ACTION_DELETE_ALL:
        $objects = $clause->find_many();		
        foreach ($objects as $o) {		
          $o->delete();		
        }		
        break;
    }
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

  /**
   * Saves a list of objects sharing a common foreign key. Reuses existing table rows:
   *   - deletes extra rows if the list is shrinking
   *   - creates extra rows if the list if growing
   * Example: if a user edits a Lexem and adds/removes LexemSources, then we can save the new list of LexemSources with
   *   LexemSource::updateList(['lexemeId' => $lexeme->id], 'sourceId', $sourceIds);
   **/
  static function updateList($filters, $field, $newValues) {
    // Select the existing rows
    $query = Model::factory(get_called_class());
    foreach ($filters as $k => $v) {
      $query = $query->where($k, $v);
    };
    $old = $query->find_many();

    // Create new rows as needed
    while (count($old) < count($newValues)) {
      $old[] = Model::factory(get_called_class())->create();
    }

    // Delete rows we no longer need
    while (count($old) > count($newValues)) {
      $dead = array_pop($old);
      $dead->delete();
    }

    // Populate data in the remaining rows
    foreach ($newValues as $i => $newValue) {
      $old[$i]->$field = $newValue;
      foreach ($filters as $k => $v) {
        $old[$i]->$k = $v;
      }
      $old[$i]->save();
    }
    
  }

  /**
   * Copies the values of all fields except id. Works better than PHP's clone operator.
   **/
  function parisClone() {
    $clone = Model::factory(get_called_class())->create();
    $fields = $this->as_array();
    foreach ($fields as $key => $value) {
      if ($key != 'id') {
        $clone->$key = $value;
      }
    }
    return $clone;
  }

  static function getField($colname, $id) {
    $result = Model::factory(get_called_class())
        ->select_many($colname)
        ->find_one($id);
    return $result->$colname;
  }

  static function _die($error, $name, $arguments) {
    printf("Error: %s in call to %s.%s, arguments: %s\n",
           $error, get_called_class(), $name, print_r($arguments, true));
    exit;
  }

}
