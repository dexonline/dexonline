<?php

class InflectedForm extends BaseObject {
  public static $_table = 'InflectedForm';

  public static function create($form = null, $lexemId = null, $inflectionId = null, $variant = null, $recommended = 1) {
    $if = Model::factory('InflectedForm')->create();
    $if->form = $form;
    $if->formNoAccent = str_replace("'", '', $form);
    $if->formUtf8General = $if->formNoAccent;
    $if->lexemId = $lexemId;
    $if->inflectionId = $inflectionId;
    $if->variant = $variant;
    $if->recommended = $recommended;
    return $if;
  }

  public static function loadByLexemId($lexemId) {
    return Model::factory('InflectedForm')->where('lexemId', $lexemId)->order_by_asc('inflectionId')->order_by_asc('variant')->find_many();
  }

  public static function loadByLexemIdMapByInflectionId($lexemId) {
    return self::mapByInflectionId(self::loadByLexemId($lexemId));
  }

  public static function loadByLexemIdMapByInflectionRank($lexemId) {
    $result = array();
    // These inflected forms have an extra field (rank) from the join
    $ifs = Model::factory('InflectedForm')->select('InflectedForm.*')->select('rank')->join('Inflection', 'inflectionId = Inflection.id')
      ->where('lexemId', $lexemId)->order_by_asc('rank')->order_by_asc('variant')->find_many();
    foreach ($ifs as $if) {
      $rank = $if->rank;
      if (!array_key_exists($rank, $result)) {
        $result[$rank] = array();
      }
      $result[$rank][] = $if;
    }
    return $result;
  }

  public static function mapByInflectionRank($ifs) {
    $result = array();
    foreach ($ifs as $if) {
      $inflection = Inflection::get_by_id($if->inflectionId);
      if (!array_key_exists($inflection->rank, $result)) {
        $result[$inflection->rank] = array();
      }
      $result[$inflection->rank][] = $if;
    }
    return $result;
  }

  public static function mapByInflectionId($ifs) {
    $result = array();
    foreach ($ifs as $if) {
      if (array_key_exists($if->inflectionId, $result)) {
        // The inflected forms are already sorted by variant
        $result[$if->inflectionId][] = $if;
      } else {
        $result[$if->inflectionId] = array($if);
      }
    }
    return $result;
  }

  public static function deleteByLexemId($lexemId) {
    $ifs = InflectedForm::get_all_by_lexemId($lexemId);
    foreach ($ifs as $if) {
      $if->delete();
    }
  }

  public function save() {
    $this->formUtf8General = $this->formNoAccent;
    parent::save();
  }  
}

?>
