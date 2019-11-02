<?php

class Medal {
  /* all medals (values in DB) */
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
  const MEDAL_EDITOR_1 = 0x800;
  const MEDAL_EDITOR_2 = 0x1000;
  const MEDAL_EDITOR_3 = 0x2000;
  const MEDAL_EDITOR_4 = 0x4000;
  const MEDAL_EDITOR_5 = 0x8000;
  const MEDAL_ARTIST_1 = 0x10000;
  const MEDAL_ARTIST_2 = 0x20000;
  const MEDAL_ARTIST_3 = 0x40000;

  /* simple medals */
  const SPONSOR_TEMPLATE = [
    'name' => 'Donator',
    'description' => '',
    'pic' => 'sponsor.png',
    'supersedes' => [],
  ];

  const SOCIAL_TEMPLATE = [
    'name' => 'Activist pe rețele sociale',
    'description' => '',
    'pic' => 'social.png',
    'supersedes' => [],
  ];

  const ARTICLES_TEMPLATE = [
    'name' => 'Editor de articole lingvistice',
    'description' => '',
    'pic' => 'articles.png',
    'supersedes' => [],
  ];

  const WOTD_TEMPLATE = [
    'name' => 'Editor al cuvântului zilei',
    'description' => '',
    'pic' => 'wotd.png',
    'supersedes' => [],
  ];

  const MODERATOR_TEMPLATE = [
    'name' => 'Moderator',
    'description' => '',
    'pic' => 'moderator.png',
    'supersedes' => [],
  ];

  /*  medals with more levels */

  // programmers medals
  const PROGRAMMER_LEVELS = [
    Medal::MEDAL_PROGRAMMER_1 => 100,
    Medal::MEDAL_PROGRAMMER_2 => 1000,
    Medal::MEDAL_PROGRAMMER_3 => 10000,
  ];

  const PROGRAMMER_TEMPLATE = [
    'name' => 'Programator (nivel %d)',
    'description' => 'peste %s de linii de cod',
    'pic' => 'programmer%d.png',
    'supersedes' => [],
  ];

  // email medals
  const EMAIL_LEVELS = [
    Medal::MEDAL_EMAIL_1 => 100,
    Medal::MEDAL_EMAIL_2 => 500,
    Medal::MEDAL_EMAIL_3 => 1000,
  ];

  const EMAIL_TEMPLATE = [
    'name' => 'Responsabil e-mail (nivel %d)',
    'description' => 'peste %s de mesaje procesate',
    'pic' => 'email%d.png',
    'supersedes' => [],
  ];

  // editor medals
  const EDITOR_LEVELS = [
    Medal::MEDAL_EDITOR_1 => 1000,
    Medal::MEDAL_EDITOR_2 => 10000,
    Medal::MEDAL_EDITOR_3 => 100000,
    Medal::MEDAL_EDITOR_4 => 1000000,
    Medal::MEDAL_EDITOR_5 => 10000000,
  ];

  const EDITOR_TEMPLATE = [
    'name' => 'Editor (nivel %d)',
    'description' => 'peste %s de caractere trimise',
    'pic' => 'editor%d.png',
    'supersedes' => [],
  ];

  //artist medals
  const ARTIST_LEVELS = [
    Medal::MEDAL_ARTIST_1 => 10,
    Medal::MEDAL_ARTIST_2 => 100,
    Medal::MEDAL_ARTIST_3 => 500,
  ];

  const ARTIST_TEMPLATE = [
    'name' => 'Desenator al cuvântului zilei (nivel %d)',
    'description' => 'minimum %s cuvinte ilustrate',
    'pic' => 'artist%d.png',
    'supersedes' => [],
  ];

  /**************************** what medals to display ***********************/
  const SIMPLE_MEDALS = [
    Medal::MEDAL_SPONSOR    => Medal::SPONSOR_TEMPLATE,
    Medal::MEDAL_SOCIAL     => Medal::SOCIAL_TEMPLATE,
    Medal::MEDAL_ARTICLES   => Medal::ARTICLES_TEMPLATE,
    Medal::MEDAL_WOTD       => Medal::WOTD_TEMPLATE,
    Medal::MEDAL_MODERATOR  => Medal::MODERATOR_TEMPLATE,
  ];

  const MEDALS_WITH_LEVELS = [
    ['level' => Medal::PROGRAMMER_LEVELS, 'template' => Medal::PROGRAMMER_TEMPLATE],
    ['level' => Medal::EMAIL_LEVELS ,     'template' => Medal::EMAIL_TEMPLATE],
    ['level' => Medal::EDITOR_LEVELS ,    'template' => Medal::EDITOR_TEMPLATE],
    ['level' => Medal::ARTIST_LEVELS ,    'template' => Medal::ARTIST_TEMPLATE],
  ];
  /*************************************************************************/

  static function getData() {
    $medalData = [];
    $medalData += self::SIMPLE_MEDALS;
    foreach (self::MEDALS_WITH_LEVELS as $medalType) {
      $medalData += self::getMedalsDataFor($medalType['level'], $medalType['template']);
    }

    return $medalData;
  }

  private static function getMedalsDataFor($levels, $template) {
    $levelCnt = 0;
    $medals = [];
    foreach ($levels as $key => $value) {
      $levelCnt++;
      $levelData = [];
      $levelData['name'] = sprintf($template['name'], $levelCnt);
      $levelData['description'] = sprintf($template['description'], number_format($value, 0, '', '.'));
      $levelData['pic'] = sprintf($template['pic'], $levelCnt);
      $levelData['supersedes'] = array_keys($medals);
      $medals[$key] = $levelData;
    }

    return $medals;
  }

  /* Returns a subset of DATA */
  static function loadForUser($user) {
    $result = [];
    $medalMask = $user ? $user->medalMask : 0;
    foreach (self::getData() as $mask => $params) {
      if ($mask & $medalMask) {
        $result[$mask] = $params;
      }
    }
    return $result;
  }

  /* Returns the canonical mask for a given mask,
     removing values which are superseded by other values */
  static function getCanonicalMask($mask) {
    foreach (self::getData() as $value => $params) {
      if ($mask & $value) {
        foreach ($params['supersedes'] as $supersedes) {
          $mask &= ~$supersedes;
        }
      }
    }
    return $mask;
  }

  static function getName($medal) {
    return self::getData()[$medal]['name'];
  }
}
