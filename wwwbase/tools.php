<?php
require_once("../phplib/util.php");

$clientOptions = array(
  'sync' => array('Sincronizare cu DEX online', 'Acest client se poate conecta periodic la DEX online pentru a-și transfera definițiile nou adăugate.'),
  'vision' => array('Interfață pentru nevăzători', 'Acest client are o interfață prietenoasă pentru nevăzători.'),
  'regexp' => array('Expresii regulate', 'Acest client acceptă căutări cu expresii regulate, cum ar fi «echi*» pentru echilibru, echinocțiu etc.'),
  'suggest' => array('Sugestii', 'Acest client oferă cele mai apropiate rezultate atunci când cuvântul căutat nu este găsit exact.'),
  'diacritics' => array('Cu / fără diacritice', 'Acest client oferă opțiuni pentru tastarea căutărilor cu sau fără diacritice.'),
  'full' => array('Căutare full-text', 'Acest client poate căuta nu doar cuvintele-titlu, ci și întreg textul definițiilor.'),
  'flex' => array('Căutare forme flexionare', 'Acest client poate căuta declinări și conjugări ale cuvintelor, cum ar fi «meargă» în loc de «merge».'),
  'click' => array('Click pe cuvânt', 'Când dați click pe un cuvânt dintr-o definiție, acest client navighează la definiția acelui cuvânt.'),
  'history' => array('Istoria căutărilor', 'Acest client ține minte ultimele cuvinte căutate și poate naviga între ele.'),
);

$clients = array(
  array('name' => 'Maestro DEX',
        'urls' => array('website' => 'http://www.tranzactiibursiere.ro/maestro/'),
        'os' => array('linux', 'windows'),
        'requires' => array('Perl', 'wxWidgets'),
        'authors' => array('Octavian Râșniță' => ''),
        'license' => 'GPL',
        'options' => array('vision' => 1, 'sync' => 1, 'regexp' => 1, 'suggest' => 1, 'diacritics' => 1, 'full' => 1, 'flex' => 1, 'click' => 1, 'history' => 1)),

  array('name' => 'JaDEX',
        'urls' => array('website' => 'http://www.federicomestrone.com/jadex/'),
        'os' => array('java', 'linux', 'mac', 'windows'),
        'requires' => array('Java'),
        'authors' => array('Federico Mestrone' => ''),
        'license' => '',
        'options' => array('vision' => 0, 'sync' => 1, 'regexp' => 1, 'suggest' => 0, 'diacritics' => 1, 'full' => 0, 'flex' => 0, 'click' => 0, 'history' => 1)),

  array('name' => 'Dixit',
        'urls' => array('website' => 'http://dixit.sourceforge.net/'),
        'os' => array('linux', 'windows'),
        'requires' => array('QT (numai sub Linux)', 'g++ (numai sub Linux)'),
        'authors' => array('Tim Anghel' => '', 'Octavian Procopiuc' => ''),
        'license' => 'GPL',
        'options' => array('vision' => 0, 'sync' => 1, 'regexp' => 1, 'suggest' => 1, 'diacritics' => 0, 'full' => 0, 'flex' => 0, 'click' => 0, 'history' => 1)),

  array('name' => 'DEX.ro',
        'urls' => array('website' => 'http://dex-ro.blogspot.com/'),
        'os' => array('windows'),
        'requires' => array(),
        'authors' => array('Ion Silvestru' => 'silvestru@yahoo.com'),
        'license' => 'Freeware',
        'options' => array('vision' => 0, 'sync' => 1, 'regexp' => 1, 'suggest' => 1, 'diacritics' => 0, 'full' => 0, 'flex' => 0, 'click' => 0, 'history' => 1)),

  array('name' => 'DEX pentru Android',
        'urls' => array('website' => 'http://dex.adrianvintu.com/', 'Market' => 'market://details?id=com.dex'),
        'os' => array('android'),
        'requires' => array(),
        'authors' => array('Adrian Vântu' => ''),
        'license' => 'Freeware',
        'options' => array('vision' => 0, 'sync' => 0, 'regexp' => 0, 'suggest' => 0, 'diacritics' => 0, 'full' => 0, 'flex' => 1, 'click' => 0, 'history' => 0)),

  array('name' => 'Pocket DEX',
        'urls' => array('website' => 'http://pocketdex.aamedia.ro/'),
        'os' => array('windowsce'),
        'requires' => array(),
        'authors' => array('Alexandru Mirea' => ''),
        'license' => 'Freeware',
        'options' => array('vision' => 0, 'sync' => 0, 'regexp' => 0, 'suggest' => 0, 'diacritics' => 0, 'full' => 0, 'flex' => 0, 'click' => 0, 'history' => 1)),

  array('name' => 'DEX Offline*',
        'urls' => array('website' => 'http://dexoffline.sourceforge.net/'),
        'os' => array('windows'),
        'requires' => array('Microsoft .NET', 'Microsoft MDAC'),
        'authors' => array('Gecko Pointdexter' => ''),
        'license' => 'Freeware',
        'options' => array('vision' => 0, 'sync' => 1, 'regexp' => 0, 'suggest' => 0, 'diacritics' => 0, 'full' => 0, 'flex' => 0, 'click' => 0, 'history' => 1)),
);

$osNames = array('android' => 'Android', 'java' => 'Java', 'linux' => 'GNU / Linux', 'mac' => 'Mac', 'windows' => 'Windows', 'windowsce' => 'Windows CE');

smarty_assign('page_title', 'DEX online - Unelte');
smarty_assign('show_search_box', 0);
smarty_assign('slick_selected', 'tools');
smarty_assign('clients', $clients);
smarty_assign('clientOptions', $clientOptions);
smarty_assign('osNames', $osNames);
smarty_displayCommonPageWithSkin('tools.ihtml');
?>
