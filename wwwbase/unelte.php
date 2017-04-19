<?php
require_once("../phplib/Core.php");

$clientOptions = [
  'sync' => [
    'Sincronizare cu dexonline',
    'Acest client se poate conecta periodic la dexonline pentru a-și transfera definițiile nou adăugate.',
  ],
  'vision' => [
    'Interfață pentru nevăzători',
    'Acest client are o interfață prietenoasă pentru nevăzători.',
  ],
  'regexp' => [
    'Expresii regulate, wildcards',
    'Acest client acceptă căutări cu expresii regulate și/sau wildcards, cum ar fi «echi*» pentru echilibru, echinocțiu etc.',
  ],
  'suggest' => [
    'Sugestii',
    'Acest client oferă cele mai apropiate rezultate atunci când cuvântul căutat nu este găsit exact.',
  ],
  'diacritics' => [
    'Cu / fără diacritice',
    'Acest client oferă opțiuni pentru tastarea căutărilor cu sau fără diacritice.',
  ],
  'full' => [
    'Căutare full-text',
    'Acest client poate căuta nu doar cuvintele-titlu, ci și întreg textul definițiilor.',
  ],
  'flex' => [
    'Căutare forme flexionare',
    'Acest client poate căuta declinări și conjugări ale cuvintelor, cum ar fi «meargă» în loc de «merge».',
  ],
  'click' => [
    'Clic pe cuvânt',
    'Când dați clic pe un cuvânt dintr-o definiție, acest client navighează la definiția acelui cuvânt.',
  ],
  'history' => [
    'Istoria căutărilor',
    'Acest client ține minte ultimele cuvinte căutate și poate naviga între ele.',
  ],
];

$clients = [
  [
    'name' => 'Maestro DEX',
    'url' => 'http://maestrodex.ro/',
    'download' => null,
    'os' => ['linux', 'windows'],
    'space' => '490 MB',
    'requires' => 'Perl, wxWidgets (numai sub Linux)',
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
    'requires' => 'QT, g++ (numai sub Linux)',
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
?>
