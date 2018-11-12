<?php
require_once("../phplib/Core.php");

$clientOptions = [
  'sync' => [
    _('Synchronization with dexonline'),
    _('This client connects to dexonline periodically to download updates.'),
  ],
  'vision' => [
    _('Interface for visually impaired users'),
    _('This client has a friendly interface for visually impaired users.')
  ],
  'regexp' => [
    _('Regular expressions, wildcards'),
    _('This client accepts searches using regular expressions and/or wildcards, such as «echi*» for echilibru, echinocțiu etc.'),
  ],
  'suggest' => [
    _('Suggestions'),
    _('This client offers the closest matches when an exact match is not found.'),
  ],
  'diacritics' => [
    _('Diacritics support'),
    _('This client supports searching with or without diacritics.'),
  ],
  'full' => [
    _('Full-text searches'),
    _('This client can search not just keywords, but the definition body as well.'),
  ],
  'flex' => [
    _('Inflected form search'),
    _('This client can query declensions and conjugations, such as «lucreze» instead of «lucra».'),
  ],
  'click' => [
    _('Word clicks'),
    _('When you click a word in a definition, this client goes to the definition for that word.'),
  ],
  'history' => [
    _('Search history'),
    _('This client remembers the most recent searches and can revisit them.'),
  ],
];

$clients = [
  [
    'name' => 'Maestro DEX',
    'url' => 'http://maestrodex.ro/',
    'download' => null,
    'os' => ['linux', 'windows'],
    'space' => '490 MB',
    'requires' => _('Perl, wxWidgets (only under Linux)'),
    'author' => ['Octavian Râșniță', ''],
    'license' => 'GPL',
    'options' => ['vision' => 1, 'sync' => 1, 'regexp' => 1, 'suggest' => 1, 'diacritics' => 1, 'full' => 1, 'flex' => 1, 'click' => 1, 'history' => 1],
  ],

  [
    'name' => 'PyDEX',
    'url' => 'http://pydex.lemonsoftware.eu/',
    'download' => null,
    'os' => ['linux', 'mac', 'windows'],
    'space' => '250 MB',
    'requires' => 'wxPython',
    'author' => ['Cristian Năvălici', ''],
    'license' => 'GPLv3',
    'options' => ['vision' => 0, 'sync' => 1, 'regexp' => 0, 'suggest' => 0, 'diacritics' => 1, 'full' => 1, 'flex' => 1, 'click' => 0, 'history' => 1],
  ],

  [
    'name' => 'Dixit',
    'url' => 'http://dixit.sourceforge.net/',
    'download' => null,
    'os' => ['linux', 'windows'],
    'space' => '48 MB',
    'requires' => _('QT, g++ (only under Linux)'),
    'author' => ['Octavian Procopiuc', ''],
    'license' => 'GPL',
    'options' => ['vision' => 0, 'sync' => 1, 'regexp' => 1, 'suggest' => 1, 'diacritics' => 0, 'full' => 0, 'flex' => 0, 'click' => 0, 'history' => 1],
  ],

  [
    'name' => 'DEX pentru Android',
    'url' => 'http://dex.adrianvintu.com/',
    'download' => 'https://play.google.com/store/search?q=pub:%22Adrian+Vintu%22',
    'os' => ['android'],
    'space' => '3 MB',
    'requires' => '',
    'author' => ['Adrian Vîntu', 'http://adrianvintu.com/'],
    'license' => 'Freeware',
    'options' => ['vision' => 0, 'sync' => 0, 'regexp' => 1, 'suggest' => 1, 'diacritics' => 0, 'full' => 0, 'flex' => 1, 'click' => 1, 'history' => 1],
  ],

  [
    'name' => 'DEX pentru Windows Phone',
    'url' => 'http://dex-wp.adrianvintu.com/',
    'download' => 'https://www.microsoft.com/en-us/store/p/dex/9nblggh09ldx',
    'os' => ['windowsPhone'],
    'space' => '1 MB',
    'requires' => '',
    'author' => ['Adrian Vîntu', 'http://adrianvintu.com/'],
    'license' => 'Freeware',
    'options' => ['vision' => 0, 'sync' => 0, 'regexp' => 1, 'suggest' => 0, 'diacritics' => 0, 'full' => 0, 'flex' => 1, 'click' => 0, 'history' => 1],
  ],

];

$osNames = [
  'android' => 'Android',
  'iphone' => 'iPhone',
  'java' => 'Java',
  'linux' => 'GNU/Linux',
  'mac' => 'Mac',
  'windows' => 'Windows',
  'windowsce' => 'Windows CE',
  'windowsPhone' =>
  'Windows Phone',
];

SmartyWrap::assign('clients', $clients);
SmartyWrap::assign('clientOptions', $clientOptions);
SmartyWrap::assign('osNames', $osNames);
SmartyWrap::display('unelte.tpl');
