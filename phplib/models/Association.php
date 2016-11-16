<?php

/**
 * An Association is a table / class that stores many-to-many mappings between
 * two other classes. Assumes that the two classes implement DatedObject.
 **/
abstract class Association extends BaseObject {
  public static $_table;

  static function create($id1, $id2) {
    $a = Model::factory(static::$_table)->create();
    $a->set(static::$fields[0], $id1);
    $a->set(static::$fields[1], $id2);
    return $a;
  }

  static function associate($id1, $id2, $swapped = false) {
    if ($swapped) {
      $tmp = $id1;
      $id1 = $id2;
      $id2 = $tmp;
    }

    // The two objects should exist
    $object1 = Model::factory(static::$classes[0])->where('id', $id1)->find_one();
    $object2 = Model::factory(static::$classes[1])->where('id', $id2)->find_one();
    if (!$object1 || !$object2) {
      return;
    }

    // The association itself should not exist
    $a = Model::factory(static::$_table)
       ->where(static::$fields[0], $id1)
       ->where(static::$fields[1], $id2)
       ->find_one();
    if (!$a) {
      $a = static::create($id1, $id2);
      $a->save();
    }

  }

  static function dissociate($id1, $id2) {
    Model::factory(static::$_table)
      ->where(static::$fields[0], $id1)
      ->where(static::$fields[1], $id2)
      ->delete_many();
  }

  /**
   * One of the arguments is an array and the other one is a numeric ID.
   * Delete all the associations of the numeric ID and reassociate it with every value in the array.
   **/
  static function wipeAndRecreate($val1, $val2) {
    $swap = is_array($val1) ? 1 : 0;
    if ($swap) {
      $tmp = $val1;
      $val1 = $val2;
      $val2 = $tmp;
    }

    // Now $val1 is numeric and $val2 is an array.
    Model::factory(static::$_table)
      ->where(static::$fields[$swap], $val1)
      ->delete_many();

    foreach ($val2 as $x) {
      self::associate($val1, $x, $swap);
    }
  }

  /**
   * Copies all the associations of $srcId to $destId. $pos can be 1 (first field) or 2
   * (second field). For example, to copy EntryLexems from lexem #123 to lexem #456, write
   * EntryLexem::copy(123, 456, 2).
   **/
  static function copy($srcId, $destId, $pos) {
    $f = ($pos == 1) ? 0 : 1;

    $associations = Model::factory(static::$_table)
                  ->where(static::$fields[$f], $srcId)
                  ->find_many();

    foreach ($associations as $a) {
      self::associate($destId, $a->get(static::$fields[1 - $f]), $f);
    }
  }

  function save() {
    parent::save();
    self::updateModDates();
  }

  function delete() {
    self::updateModDates();
    parent::delete();
  }

  function updateModDates() {
    for ($i = 0; $i < 2; $i++) {
      $query = sprintf("update %s set modDate = unix_timestamp() where id = %s",
                       static::$classes[$i],
                       $this->get(static::$fields[$i]));
      db_execute($query);
    }
  }

}

?>
