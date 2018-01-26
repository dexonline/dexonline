<?php

class InflectedForm extends BaseObject {
  public static $_table = 'InflectedForm';

  static function create($form = null, $lexemeId = null, $inflectionId = null,
                                $variant = null, $recommended = 1) {
    $if = Model::factory('InflectedForm')->create();
    $if->form = $form;
    $if->formNoAccent = str_replace("'", '', $form);
    $if->formUtf8General = $if->formNoAccent;
    $if->lexemeId = $lexemeId;
    $if->inflectionId = $inflectionId;
    $if->variant = $variant;
    $if->recommended = $recommended;
    return $if;
  }

  function getHtmlForm() {
    return Str::highlightAccent($this->form);
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

  function save() {
    $this->formUtf8General = $this->formNoAccent;
    parent::save();
  }  
}
