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
  const MEDAL_EDITOR_1 = 0x800;
  const MEDAL_EDITOR_2 = 0x1000;
  const MEDAL_EDITOR_3 = 0x2000;
  const MEDAL_EDITOR_4 = 0x4000;
  const MEDAL_EDITOR_5 = 0x8000;
  const MEDAL_ARTIST_1 = 0x10000;
  const MEDAL_ARTIST_2 = 0x20000;
  const MEDAL_ARTIST_3 = 0x40000;

  const PROGRAMMER_LEVELS = [
    Medal::MEDAL_PROGRAMMER_1 => 100,
    Medal::MEDAL_PROGRAMMER_2 => 1000,
    Medal::MEDAL_PROGRAMMER_3 => 10000,
  ];

  const PROGRAMMER_TEMPLATE = [
    'name' => 'Programator (nivel %d)',
    'description' => 'peste %s de linii de cod',
    'pic' => 'programmer%d.png',
  ];

  const EMAIL_LEVELS = [
    Medal::MEDAL_EMAIL_1 => 100,
    Medal::MEDAL_EMAIL_2 => 500,
    Medal::MEDAL_EMAIL_3 => 1000,
  ];

  const EMAIL_TEMPLATE = [
    'name' => 'Responsabil e-mail (nivel %d)',
    'description' => 'peste %s de mesaje procesate',
    'pic' => 'email%d.png',
  ];

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
  ];

  const ARTIST_LEVELS = [
    Medal::MEDAL_ARTIST_1 => 10,
    Medal::MEDAL_ARTIST_2 => 100,
    Medal::MEDAL_ARTIST_3 => 500,
  ];

  const ARTIST_TEMPLATE = [
    'name' => 'Desenator al cuvântului zilei (nivel %d)',
    'description' => 'minimum %s cuvinte ilustrate',
    'pic' => 'artist%d.png',
  ];

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

  static function getData() {
    $medalData = [];

    $simpleMedals = [
      self::MEDAL_SPONSOR => [
        'name' => 'Donator',
        'description' => '',
        'pic' => 'sponsor.png',
        'supersedes' => [],
      ],
      self::MEDAL_SOCIAL => [
        'name' => 'Activist pe rețele sociale',
        'description' => '',
        'pic' => 'social.png',
        'supersedes' => [],
      ],
      self::MEDAL_ARTICLES => [
        'name' => 'Editor de articole lingvistice',
          'description' => '',
          'pic' => 'articles.png',
          'supersedes' => [],
        ],
      self::MEDAL_WOTD => [
        'name' => 'Editor al cuvântului zilei',
        'description' => '',
        'pic' => 'wotd.png',
        'supersedes' => [],
      ],
      self::MEDAL_MODERATOR => [
        'name' => 'Moderator',
        'description' => '',
        'pic' => 'moderator.png',
        'supersedes' => [],
      ]
    ];
    $medalData += $simpleMedals;
    $medalData += self::getMedalsDataFor(self::PROGRAMMER_LEVELS, self::PROGRAMMER_TEMPLATE);
    $medalData += self::getMedalsDataFor(self::EMAIL_LEVELS, self::EMAIL_TEMPLATE);
    $medalData += self::getMedalsDataFor(self::EDITOR_LEVELS, self::EDITOR_TEMPLATE);
    $medalData += self::getMedalsDataFor(self::ARTIST_LEVELS, self::ARTIST_TEMPLATE);

    return $medalData;
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

  /* Returns the canonical mask for a given mask, removing values which are
     superseded by other values */
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
