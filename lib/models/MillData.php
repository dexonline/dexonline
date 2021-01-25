<?php

class MillData extends BaseObject implements DatedObject {
  public static $_table = 'MillData';

  // Not rigorous -- we are interested in the kind of inflection each meaning
  // has so that, for example, we don't show verbs as choices for an obvious
  // noun.
  const POS_NOUN = 0x01;
  const POS_ADJECTIVE = 0x02;
  const POS_VERB = 0x04;
  const POS_INVARIABLE = 0x08;

  /**
   * Returns a bit mask of part of speeches by looking at lexemes which
   *   (1) are associated with the tree and
   *   (2) have the exact same description
   */
  static function getPosMask($treeId, $form) {
    $pos = Model::factory('Lexeme')
      ->table_alias('l')
      ->select('l.modelType')
      ->join('EntryLexeme', ['l.id', '=', 'el.lexemeId'], 'el')
      ->join('TreeEntry', ['el.entryId', '=', 'te.entryId'], 'te')
      ->where('te.treeId', $treeId)
      ->where('l.formNoAccent', $form)
      ->find_array();

    $posMask = 0;

    foreach ($pos as $rec) {
      switch ($rec['modelType']) {
        case 'F':
        case 'IL':
        case 'M':
        case 'MF':
        case 'N':
        case 'P':
          $posMask |= self::POS_NOUN; break;

        case 'A':
        case 'AF':
        case 'AM':
        case 'AN':
        case 'PT':
          $posMask |= self::POS_ADJECTIVE; break;

        case 'V':
        case 'VT':
          $posMask |= self::POS_VERB; break;

        case 'I':
          $posMask |= self::POS_INVARIABLE; break;

          // skip others like T, SP, NL, FF
      }
    }

    return $posMask;
  }
}
