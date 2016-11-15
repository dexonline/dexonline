<?php

/**
 * An Association is a table / class that stores many-to-many mappings between
 * two other classes. Assumes that the two classes implement DatedObject.
 **/
abstract class Association extends BaseObject {
  public static $_table;

  // Try to guess class / field names. Child classes may override this function to provide
  // names explicitly. By default, we assume that:
  //
  // * the child class follows the naming convention 'FooBar';
  // * its fields are 'fooId' and 'barId'
  // * the corresponding tables are 'Foo' and 'Bar' with the primary key field 'id'.
  protected static function getFields() {
    preg_match_all('/([A-Z][a-z]+)/', static::$_table, $matches);

    if (!isset($matches[0]) || (count($matches[0]) != 2)) {
      throw new Exception(sprintf('Cannot parse default class/field names for association "%s"',
                                  static::$_table));
    }

    return [
      'class1' => $matches[0][0],
      'class2' => $matches[0][1],
      'field1' => strtolower($matches[0][0]) . "Id",
      'field2' => strtolower($matches[0][1]) . "Id",
    ];
  }

  static function create($id1, $id2) {
    $f = static::getFields();

    $a = Model::factory(static::$_table)->create();
    $a->set($f['field1'], $id1);
    $a->set($f['field2'], $id2);
    return $a;
  }

  static function associate($id1, $id2) {
    $f = static::getFields();

    // The two objects should exist
    $object1 = Model::factory($f['class1'])->where('id', $id1)->find_one();
    $object2 = Model::factory($f['class2'])->where('id', $id2)->find_one();
    if (!$object1 || !$object2) {
      return;
    }

    // The association itself should not exist
    $a = Model::factory(static::$_table)
       ->where($f['field1'], $id1)
       ->where($f['field2'], $id2)
       ->find_one();
    if (!$a) {
      $a = static::create($id1, $id2);
      $a->save();
    }

  }

  static function dissociate($id1, $id2) {
    $f = static::getFields();

    Model::factory(static::$_table)
      ->where($f['field1'], $id1)
      ->where($f['field2'], $id2)
      ->delete_many();
  }

  /**
   * One of the arguments is an array and the other one is a numeric ID.
   * Delete all the associations of the numeric ID and reassociate it with every value in the array.
   **/
  static function wipeAndRecreate($val1, $val2) {
    $f = static::getFields();

    if (is_array($val1)) {
      Model::factory(static::$_table)
        ->where($f['field2'], $val2)
        ->delete_many();

      foreach ($val1 as $x) {
        self::associate($x, $val2);
      }
    } else {
      Model::factory(static::$_table)
        ->where($f['field1'], $val1)
        ->delete_many();

      foreach ($val2 as $x) {
        self::associate($val1, $x);
      }
    }
  }

  /**
   * Copies all the associations of $srcId to $destId. $pos can be 1 (first field) or 2
   * (second field). For example, to copy EntryLexems from lexem #123 to lexem #456, use
   * EntryLexem::copy(123, 456, 2).
   **/
  static function copy($srcId, $destId, $pos) {
    $f = static::getFields();

    if ($pos == 1) {
      $associations = Model::factory(static::$_table)
                    ->where($f['field1'], $srcId)
                    ->find_many();
      foreach ($associations as $a) {
        self::associate($destId, $a->get($f['field2']));
      }
    } else {
      $associations = Model::factory(static::$_table)
                    ->where($f['field2'], $srcId)
                    ->find_many();
      foreach ($associations as $a) {
        self::associate($a->get($f['field1']), $destId);
      }
    }
  }

  function save() {
    $f = static::getFields();

    parent::save();
    self::updateModDate($f['class1'], $this->get($f['field1']));
    self::updateModDate($f['class2'], $this->get($f['field2']));
  }

  function delete() {
    $f = static::getFields();

    self::updateModDate($f['class1'], $this->get($f['field1']));
    self::updateModDate($f['class2'], $this->get($f['field2']));
    parent::delete();
  }

  static function updateModDate($class, $id) {
    return db_execute("update {$class} set modDate = unix_timestamp() where id = {$id}");
  }

}

?>
