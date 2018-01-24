<?php

/**
 * An Association is a table / class that stores many-to-many mappings between
 * two other classes. We use FooBar as a hypotetical example throughout this class.
 *
 * Associations have the structure:
 * - table name: FooBar, mapping Foos to Bars;
 * - foreign keys: fooId, barId;
 * - rank fields: fooRank, barRank.
 * - possibly other fields called payload (e.g. EntryLexeme.main)
 *
 * fooRank stores the rank of this Foo in the list of Foos for this
 * Bar. Ranks don't have to be consecutive, which speeds up deletions
 * (no need to rerank). We use FooBar.id as a tie breaker, which
 * speeds up insertions (just set rank to a very large value).
 **/
abstract class Association extends BaseObject {
  public static $_table;

  const LAST = 1000000;

  const PLURALS = [
    'Definitions' => 'Definition',
    'Entries' => 'Entry',
    'Lexems' => 'Lexem',
    'Meanings' => 'Meaning',
    'Sources' => 'Source',
    'Trees' => 'Tree',
  ];

  static function create($id1,
                         $id2,
                         $payload = [],
                         $rank1 = self::LAST,
                         $rank2 = self::LAST) {
    $a = Model::factory(static::$_table)->create();
    $a->set(static::$fields[0], $id1);
    $a->set(static::$fields[1], $id2);
    $a->set(static::$ranks[0], $rank1);
    $a->set(static::$ranks[1], $rank2);
    foreach ($payload as $key => $value) {
      $a->set($key, $value);
    }
    return $a;
  }

  static function associate($id1, $id2, $payload = [], $rank1 = self::LAST, $rank2 = self::LAST) {
    // The two objects should exist
    $object1 = Model::factory(static::$classes[0])->where('id', $id1)->find_one();
    $object2 = Model::factory(static::$classes[1])->where('id', $id2)->find_one();
    if (!$object1 || !$object2) {
      return;
    }

    // The association itself should not exist
    $a = Model::factory(static::$_table)
       ->where(static::$fields[0], $id1)
       ->where(static::$fields[1], $id2);
    foreach ($payload as $key => $value) {
      $a = $a->where($key, $value);
    }
    $a =$a->find_one();
    if (!$a) {
      $a = static::create($id1, $id2, $payload, $rank1, $rank2);
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
   * Copies all the associations of $srcId to $destId. $pos can be 1 (first field) or 2
   * (second field). For example, to copy FooBars from bar #123 to bar #456, write
   * FooBar::copy(123, 456, 2).
   **/
  static function copy($srcId, $destId, $pos, $payload = []) {
    $f = ($pos == 1) ? 0 : 1;

    $associations = Model::factory(static::$_table)
                  ->where(static::$fields[$f], $srcId);
    foreach ($payload as $key => $value) {
      $associations = $associations->where($key, $value);
    }
    $associations = $associations
                  ->order_by_asc(static::$ranks[1 - $f])
                  ->order_by_asc('id')
                  ->find_many();

    // count existing Foos for this Bar so that new Foos are numbered incrementally
    // we could also just set all new ranks to LAST, but this is more paranoid
    $count = Model::factory(static::$_table)
           ->where(static::$fields[$f], $destId)
           ->count();

    // For field $pos copy the rank (so that the destination remains at the same position in the list
    // as the original. For the other field, number ranks incrementally.
    foreach ($associations as $a) {
      if ($f) {
        // swap order
        self::associate($a->get(static::$fields[1 - $f]), $destId, $payload,
                        ++$count, $a->get(static::$ranks[$f]));
      } else {
        self::associate($destId, $a->get(static::$fields[1 - $f]), $payload,
                        $a->get(static::$ranks[$f]), ++$count);
      }
    }
  }

  /**
   * $left is either a Foo ID or an array of Foo IDs.
   * $right is either a Bar ID or an array of Bar IDs.
   * $payload is an array of key => values to filter the associations
   * exactly one of $left and $right is an array.
   * This function associates the numeric ID to every ID in the array.
   * Performs insertions / updates / deletions as necessary.
   **/
  static function update($left, $right, $payload = []) {
    // make sure that $left is numeric and $right is an array
    $swap = is_array($left) ? 1 : 0;
    if ($swap) {
      $tmp = $left;
      $left = $right;
      $right = $tmp;
    }

    // load existing associations
    $old = Model::factory(static::$_table)
         ->where(static::$fields[$swap], $left);
    foreach ($payload as $key => $value) {
      $old = $old->where($key, $value);
    }
    $old = $old->find_many();

    // map them by the IDs in the array
    $map = [];
    foreach ($old as $assoc) {
      $id = $assoc->get(static::$fields[1 - $swap]);
      $map[$id] = $assoc;
    }

    // iterate the array, creating or updating records
    $rank = 1;
    foreach ($right as $id) {
      if (isset($map[$id])) {
        // existing association, update the order
        $map[$id]->set(static::$ranks[1 - $swap], $rank);
        $map[$id]->save();
        unset($map[$id]);
      } else {
        // create new association
        $a = $swap
           ? self::create($id, $left, $payload, $rank, self::LAST)
           : self::create($left, $id, $payload, self::LAST, $rank);
        $a->save();
      }
      $rank++;
    }

    // delete leftover associations
    foreach ($map as $assoc) {
      $assoc->delete();
    }
  }
}
