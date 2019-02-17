<?php

class SourceType extends BaseObject {
  public static $_table = 'SourceType';

  const TYPE_DICT_GENERAL_USE = 1;
  const TYPE_DICT_MORPHOLOGICAL = 2;
  const TYPE_DICT_RELATIONAL = 3;
  const TYPE_DICT_ETYMOLOGICAL = 4;
  const TYPE_DICT_SPECIALIZED = 5;
  const TYPE_DICT_ENCYCLOPEDIC = 6;
  const TYPE_DICT_SLANG = 7;
  const TYPE_DICT_OTHER = 999;
  const TYPE_DICT_UNVERIFIED = 1000;

  const SOURCE_TYPE_NAMES = array(
    self::TYPE_DICT_GENERAL_USE => 'General use dictionaries',
    self::TYPE_DICT_MORPHOLOGICAL => 'Morphological dictionaries',
    self::TYPE_DICT_RELATIONAL => 'Relational dictionaries',
    self::TYPE_DICT_ETYMOLOGICAL => 'Etymological dictionaries',
    self::TYPE_DICT_SPECIALIZED => 'Specialized dictionaries',
    self::TYPE_DICT_ENCYCLOPEDIC => 'Encyclopedic dictionaries',
    self::TYPE_DICT_SLANG => 'Slang dictionaries',
    self::TYPE_DICT_OTHER => 'Other dictionaries',
    self::TYPE_DICT_UNVERIFIED => 'Unverified dictionaries'
  );

  const SOURCE_TYPE_DESCRIPTION = array(
    self::TYPE_DICT_GENERAL_USE => 'The most common sense of the words are explained.',
    self::TYPE_DICT_MORPHOLOGICAL => 'The correspondences between lemma and lexical forms of words.',
    self::TYPE_DICT_RELATIONAL => 'These are not definitions, but relations between words.',
    self::TYPE_DICT_ETYMOLOGICAL => 'The etymology of (family of) words are explained.',
    self::TYPE_DICT_SPECIALIZED => 'These definitions usually explain only specialized meanings of words.',
    self::TYPE_DICT_ENCYCLOPEDIC => 'Encyclopedic definitions',
    self::TYPE_DICT_SLANG => 'Only slang words or senses are defined.',
    self::TYPE_DICT_OTHER => 'These definitions could explain only certain meanings of words.',
    self::TYPE_DICT_UNVERIFIED => 'Since they are not made by lexicographers, these definitions may contain errors.'
  );

  static function getDictTypes() {
    return array(
      'label' => self::SOURCE_TYPE_NAMES,
      'desc'  => self::SOURCE_TYPE_DESCRIPTION
    );
  }

  static function getAll() {
    $query = Model::factory(SourceType::$_table);
    return $query->find_many();
  }
}