<?php

class InflectedForm extends BaseObject {
  function __construct($form = null, $lexemId = null, $inflectionId = null, $variant = null, $recommended = 1) {
    parent::__construct();
    $this->form = $form;
    $this->formNoAccent = str_replace("'", '', $form);
    $this->formUtf8General = $this->formNoAccent;
    $this->lexemId = $lexemId;
    $this->inflectionId = $inflectionId;
    $this->variant = $variant;
    $this->recommended = $recommended;
  }

  public static function loadByLexemId($lexemId) {
    return db_find(new InflectedForm(), "lexemId = {$lexemId} order by inflectionId, variant");
  }

  public static function loadByLexemIdMapByInflectionId($lexemId) {
    return self::mapByInflectionId(self::loadByLexemId($lexemId));
  }

  public static function loadByLexemIdMapByInflectionRank($lexemId) {
    $result = array();
    // Sadly, we cannot load the rank here as well, because $if->set($dbResult->fields) would no longer work.
    $dbResult = db_execute("select InflectedForm.*, rank from InflectedForm, Inflection where inflectionId = Inflection.id and lexemId = {$lexemId} order by rank, variant");
    while (!$dbResult->EOF) {
      $rank = $dbResult->fields['rank'];
      array_pop($dbResult->fields); // Pop the ['rank'] and [nnn] keys;
      array_pop($dbResult->fields); // otherwise $if->set() won't work -- AdoDB panics because of the extra fields.
      $if = new InflectedForm();
      $if->set($dbResult->fields);
      if (!array_key_exists($rank, $result)) {
        $result[$rank] = array();
      }
      $result[$rank][] = $if;
      $dbResult->MoveNext();
    }
    return $result;
  }

  public static function mapByInflectionRank($ifs) {
    $result = array();
    foreach ($ifs as $if) {
      $inflection = Inflection::get("id = {$if->inflectionId}");
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
    $ifs = db_find(new InflectedForm(), "lexemId = {$lexemId}");
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
