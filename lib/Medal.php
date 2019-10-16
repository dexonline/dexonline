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

  const EDITOR_LEVELS = [
    Medal::MEDAL_EDITOR_5 => 10000000,
    Medal::MEDAL_EDITOR_4 => 1000000,
    Medal::MEDAL_EDITOR_3 => 100000,
    Medal::MEDAL_EDITOR_2 => 10000,
    Medal::MEDAL_EDITOR_1 => 1000,
  ];

  const ARTIST_LEVELS = [
    Medal::MEDAL_ARTIST_3 => 500,
    Medal::MEDAL_ARTIST_2 => 100,
    Medal::MEDAL_ARTIST_1 => 10,
  ];

  const EMAIL_LEVELS = [
    Medal::MEDAL_EMAIL_3 => 1000,
    Medal::MEDAL_EMAIL_2 => 500,
    Medal::MEDAL_EMAIL_1 => 100,
  ];

  const PROGRAMMER_LEVELS = [
    Medal::MEDAL_PROGRAMMER_3 => 10000,
    Medal::MEDAL_PROGRAMMER_2 => 1000,
    Medal::MEDAL_PROGRAMMER_1 => 100,
  ];

  static function getData() {
    return [
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
      self::MEDAL_ARTICLES =>
        ['name' => 'Editor de articole lingvistice',
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
      ],
      self::MEDAL_PROGRAMMER_1 => [
        'name' => 'Programator (nivel 1)',
        'description' => 'peste ' . number_format(self::PROGRAMMER_LEVELS[self::MEDAL_PROGRAMMER_1], 0, '', '.') . ' de linii de cod',
        'pic' => 'programmer1.png',
        'supersedes' => [],
      ],
      self::MEDAL_PROGRAMMER_2 => [
        'name' => 'Programator (nivel 2)',
        'description' => 'peste ' . number_format(self::PROGRAMMER_LEVELS[self::MEDAL_PROGRAMMER_2], 0, '', '.') . ' de linii de cod',
        'pic' => 'programmer2.png',
        'supersedes' => [self::MEDAL_PROGRAMMER_1],
      ],
      self::MEDAL_PROGRAMMER_3 => [
        'name' => 'Programator (nivel 3)',
        'description' => 'peste ' . number_format(self::PROGRAMMER_LEVELS[self::MEDAL_PROGRAMMER_3], 0, '', '.') . ' de linii de cod',
        'pic' => 'programmer3.png',
        'supersedes' => [self::MEDAL_PROGRAMMER_1, self::MEDAL_PROGRAMMER_2],
      ],
      self::MEDAL_EMAIL_1 => [
        'name' => 'Responsabil e-mail (nivel 1)',
        'description' => 'peste ' . number_format(self::EMAIL_LEVELS[self::MEDAL_EMAIL_1], 0, '', '.') . ' de mesaje procesate',
        'pic' => 'email1.png',
        'supersedes' => [],
      ],
      self::MEDAL_EMAIL_2 => [
        'name' => 'Responsabil e-mail (nivel 2)',
        'description' => 'peste ' . number_format(self::EMAIL_LEVELS[self::MEDAL_EMAIL_2], 0, '', '.') . ' de mesaje procesate',
        'pic' => 'email2.png',
        'supersedes' => [self::MEDAL_EMAIL_1],
      ],
      self::MEDAL_EMAIL_3 => [
        'name' => 'Responsabil e-mail (nivel 3)',
        'description' => 'peste ' . number_format(self::EMAIL_LEVELS[self::MEDAL_EMAIL_3], 0, '', '.') . ' de mesaje procesate',
        'pic' => 'email3.png',
        'supersedes' => [
          self::MEDAL_EMAIL_1,
          self::MEDAL_EMAIL_2
        ],
      ],
      self::MEDAL_EDITOR_1 => [
        'name' => 'Editor (nivel 1)',
        'description' => 'peste ' . number_format(self::EDITOR_LEVELS[self::MEDAL_EDITOR_1], 0, '', '.') . ' de caractere trimise',
        'pic' => 'editor1.png',
        'supersedes' => [],
      ],
      self::MEDAL_EDITOR_2 => [
        'name' => 'Editor (nivel 2)',
        'description' => 'peste ' . number_format(self::EDITOR_LEVELS[self::MEDAL_EDITOR_2], 0, '', '.') . ' de caractere trimise',
        'pic' => 'editor2.png',
        'supersedes' => [self::MEDAL_EDITOR_1],
      ],
      self::MEDAL_EDITOR_3 => [
        'name' => 'Editor (nivel 3)',
        'description' => 'peste ' . number_format(self::EDITOR_LEVELS[self::MEDAL_EDITOR_3], 0, '', '.') . ' de caractere trimise',
        'pic' => 'editor3.png',
        'supersedes' => [
          self::MEDAL_EDITOR_1,
          self::MEDAL_EDITOR_2
        ],
      ],
      self::MEDAL_EDITOR_4 => [
        'name' => 'Editor (nivel 4)',
        'description' => 'peste ' . number_format(self::EDITOR_LEVELS[self::MEDAL_EDITOR_4], 0, '', '.') . ' de caractere trimise',
        'pic' => 'editor4.png',
        'supersedes' => [
          self::MEDAL_EDITOR_1,
          self::MEDAL_EDITOR_2,
          self::MEDAL_EDITOR_3
        ],
      ],
      self::MEDAL_EDITOR_5 => [
        'name' => 'Editor (nivel 5)',
        'description' => 'peste ' . number_format(self::EDITOR_LEVELS[self::MEDAL_EDITOR_5], 0, '', '.') . ' de caractere trimise',
        'pic' => 'editor5.png',
        'supersedes' => [
          self::MEDAL_EDITOR_1,
          self::MEDAL_EDITOR_2,
          self::MEDAL_EDITOR_3,
          self::MEDAL_EDITOR_4,
        ],
      ],
      self::MEDAL_ARTIST_1 => [
        'name' => 'Desenator al cuvântului zilei (nivel 1)',
        'description' => 'minimum ' . number_format(self::ARTIST_LEVELS[self::MEDAL_ARTIST_1], 0, '', '.') . ' cuvinte ilustrate',
        'pic' => 'artist1.png',
        'supersedes' => [],
      ],
      self::MEDAL_ARTIST_2 => [
        'name' => 'Desenator al cuvântului zilei (nivel 2)',
        'description' => 'minimum ' . number_format(self::ARTIST_LEVELS[self::MEDAL_ARTIST_2], 0, '', '.') . ' cuvinte ilustrate',
        'pic' => 'artist2.png',
        'supersedes' => [self::MEDAL_ARTIST_1],
      ],
      self::MEDAL_ARTIST_3 => [
        'name' => 'Desenator al cuvântului zilei (nivel 3)',
        'description' => 'minimum ' . number_format(self::ARTIST_LEVELS[self::MEDAL_ARTIST_3], 0, '', '.') . ' cuvinte ilustrate',
        'pic' => 'artist3.png',
        'supersedes' => [
          self::MEDAL_ARTIST_1,
          self::MEDAL_ARTIST_2
        ],
      ],
    ];
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

  /* Returns the canonical mask for a given mask, removing values which are superseded by other values */
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
    return self::DATA[$medal]['name'];
  }
}
