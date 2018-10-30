<?php

class InflectedForm extends BaseObject {
  public static $_table = 'InflectedForm';

  static function create($form = null, $lexemeId = null, $inflectionId = null,
                                $variant = null, $recommended = 1) {
    $if = Model::factory('InflectedForm')->create();
    $if->form = $form;
    $if->formNoAccent = preg_replace("/(?<!\\\\)'/", '', $form);
    $if->formNoAccent = str_replace("\\'", "'", $if->formNoAccent);
    $if->formUtf8General = $if->formNoAccent;
    $if->lexemeId = $lexemeId;
    $if->inflectionId = $inflectionId;
    $if->variant = $variant;
    $if->recommended = $recommended;
    return $if;
  }

  function getHtmlForm() {
    $s = Str::highlightAccent($this->form);
    $s = str_replace("\\'", "'", $s);
    return $s;
  }

  static function mapByInflectionRank($ifs) {
    $result = [];
    foreach ($ifs as $if) {
      $inflection = Inflection::get_by_id($if->inflectionId);
      if (!array_key_exists($inflection->rank, $result)) {
        $result[$inflection->rank] = [];
      }
      $result[$inflection->rank][] = $if;
    }
    return $result;
  }

  // The inflection ID implies the correct canonical model type
  static function deleteByModelNumberInflectionId($modelNumber, $inflId) {
    // Idiorm doesn't support deletes with joins
    DB::execute(sprintf("
      delete i
      from InflectedForm i
      join Lexeme l on i.lexemeId = l.id
      where l.modelNumber = '%s' and i.inflectionId = %d
    ", addslashes($modelNumber), $inflId));
  }

  static function isStopWord($field, $form) {
    return Model::factory('InflectedForm')
      ->table_alias('i')
      ->join('Lexeme', 'i.lexemeId = l.id', 'l')
      ->where("i.{$field}", $form)
      ->where('l.stopWord', 1)
      ->count();
  }

  function save() {
    $this->formUtf8General = $this->formNoAccent;
    parent::save();
  }
}
