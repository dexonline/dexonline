<?php
/**
 * Returns usable constraints in context of modelType
 */
class Constraint extends BaseObject {
  public static $_table = 'Constraint';
  public $selected = false;

  static function getForModelType($modelType) {
    $join = 'BINARY c.`code` =  BINARY cm.`code`';
    $result = Model::factory(static::$_table)
      ->table_alias('c')
      ->inner_join('ConstraintMap', $join, 'cm')
      ->inner_join('Inflection', [ 'i.id', '=', 'cm.inflectionId' ], 'i')
      ->select(['c.code', 'c.description'])
      ->where('i.modelType', $modelType)
      ->group_by([ 'cm.code', 'cm.variant' ])
      ->order_by_asc('c.id')
      ->find_many();

    return $result;
  }
}
