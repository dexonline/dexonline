<?php

class Medal {
  const MEDAL_SPONSOR = 0x1;
  const MEDAL_SOCIAL = 0x2;
  const MEDAL_ARTICLES = 0x4;
  const MEDAL_WOTD = 0x8;
  const MEDAL_MODERATOR = 0x10;
  const MEDAL_PROGRAMMER_1 = 0x20;
  const MEDAL_PROGRAMMER_2 = 0x40;
  const MEDAL_PROGRAMMER_3 = 0x80;
  const MEDAL_EMAIL_1 = 0x100;
  const MEDAL_EMAIL_2 = 0x200;
  const MEDAL_EMAIL_3 = 0x400;
  const MEDAL_VOLUNTEER_1 = 0x800;
  const MEDAL_VOLUNTEER_2 = 0x1000;
  const MEDAL_VOLUNTEER_3 = 0x2000;
  const MEDAL_VOLUNTEER_4 = 0x4000;
  const MEDAL_VOLUNTEER_5 = 0x8000;

  static $DATA = array(self::MEDAL_SPONSOR =>
                       array('name' => 'Donator',
                             'description' => '',
                             'pic' => 'sponsor.png',
                             'supersedes' => array()),
                       self::MEDAL_SOCIAL =>
                       array('name' => 'Activist pe rețele sociale',
                             'description' => '',
                             'pic' => 'social.png',
                             'supersedes' => array()),
                       self::MEDAL_ARTICLES =>
                       array('name' => 'Editor de articole lingvistice',
                             'description' => '',
                             'pic' => 'articles.png',
                             'supersedes' => array()),
                       self::MEDAL_WOTD =>
                       array('name' => 'Editor al cuvântului zilei',
                             'description' => '',
                             'pic' => 'wotd.png',
                             'supersedes' => array()),
                       self::MEDAL_MODERATOR =>
                       array('name' => 'Moderator',
                             'description' => '',
                             'pic' => 'moderator.png',
                             'supersedes' => array()),
                       self::MEDAL_PROGRAMMER_1 =>
                       array('name' => 'Programator (nivel 1)',
                             'description' => 'peste 100 de linii de cod',
                             'pic' => 'programmer1.png',
                             'supersedes' => array()),
                       self::MEDAL_PROGRAMMER_2 =>
                       array('name' => 'Programator (nivel 2)',
                             'description' => 'peste 1.000 de linii de cod',
                             'pic' => 'programmer2.png',
                             'supersedes' => array(self::MEDAL_PROGRAMMER_1)),
                       self::MEDAL_PROGRAMMER_3 =>
                       array('name' => 'Programator (nivel 3)',
                             'description' => 'peste 10.000 de linii de cod',
                             'pic' => 'programmer3.png',
                             'supersedes' => array(self::MEDAL_PROGRAMMER_1, self::MEDAL_PROGRAMMER_2)),
                       self::MEDAL_EMAIL_1 =>
                       array('name' => 'Responsabil e-mail (nivel 1)',
                             'description' => 'peste 100 de mesaje procesate',
                             'pic' => 'email1.png',
                             'supersedes' => array()),
                       self::MEDAL_EMAIL_2 =>
                       array('name' => 'Responsabil e-mail (nivel 2)',
                             'description' => 'peste 500 de mesaje procesate',
                             'pic' => 'email2.png',
                             'supersedes' => array(self::MEDAL_EMAIL_1)),
                       self::MEDAL_EMAIL_3 =>
                       array('name' => 'Responsabil e-mail (nivel 3)',
                             'description' => 'peste 1.000 de mesaje procesate',
                             'pic' => 'email3.png',
                             'supersedes' => array(self::MEDAL_EMAIL_1, self::MEDAL_EMAIL_2)),
                       self::MEDAL_VOLUNTEER_1 =>
                       array('name' => 'Voluntar (nivel 1)',
                             'description' => 'peste 1.000 de caractere trimise',
                             'pic' => 'volunteer1.png',
                             'supersedes' => array()),
                       self::MEDAL_VOLUNTEER_2 =>
                       array('name' => 'Voluntar (nivel 2)',
                             'description' => 'peste 10.000 de caractere trimise',
                             'pic' => 'volunteer2.png',
                             'supersedes' => array(self::MEDAL_VOLUNTEER_1)),
                       self::MEDAL_VOLUNTEER_3 =>
                       array('name' => 'Voluntar (nivel 3)',
                             'description' => 'peste 100.000 de caractere trimise',
                             'pic' => 'volunteer3.png',
                             'supersedes' => array(self::MEDAL_VOLUNTEER_1, self::MEDAL_VOLUNTEER_2)),
                       self::MEDAL_VOLUNTEER_4 =>
                       array('name' => 'Voluntar (nivel 4)',
                             'description' => 'peste 1.000.000 de caractere trimise',
                             'pic' => 'volunteer4.png',
                             'supersedes' => array(self::MEDAL_VOLUNTEER_1, self::MEDAL_VOLUNTEER_2, self::MEDAL_VOLUNTEER_3)),
                       self::MEDAL_VOLUNTEER_5 =>
                       array('name' => 'Voluntar (nivel 5)',
                             'description' => 'peste 10.000.000 de caractere trimise',
                             'pic' => 'volunteer5.png',
                             'supersedes' => array(self::MEDAL_VOLUNTEER_1, self::MEDAL_VOLUNTEER_2, self::MEDAL_VOLUNTEER_3, self::MEDAL_VOLUNTEER_4)),
                       );

  /* Returns a subset of $DATA */
  static function loadForUser($user) {
    $result = array();
    $medalMask = $user ? $user->medalMask : 0;
    foreach (self::$DATA as $mask => $params) {
      if ($mask & $medalMask) {
        $result[$mask] = $params;
      }
    }
    return $result;
  }

  /* Returns the canonical mask for a given mask, removing values which are superseded by other values */
  static function getCanonicalMask($mask) {
    foreach (self::$DATA as $value => $params) {
      if ($mask & $value) {
        foreach ($params['supersedes'] as $supersedes) {
          $mask &= ~$supersedes;
        }
      }
    }
    return $mask;
  }
}

?>
