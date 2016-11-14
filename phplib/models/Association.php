<?php

/**
 * An Association is a table / class that stores many-to-many mappings between
 * two other classes.
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
    preg_match_all('/((?:^|[A-Z])[a-z]+)/', static::$_table, $matches);

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

  public static function associate($id1, $id2) {
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
      $a = Model::factory(static::$_table)->create();
      $a->set($f['field1'], $id1);
      $a->set($f['field2'], $id2);
      $a->save();
    }

  }
}

?>
