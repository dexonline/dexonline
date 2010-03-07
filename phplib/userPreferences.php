<?php

// Set of all customizable user preferences
$userPreferencesSet = array(
  'CEDILLA_BELOW' => array(
	  'value' => 1 << 0,
    'label' => 'Vreau să văd ş și ţ cu sedilă (în loc de virguliță)',
    'comment' => 'Scrierea corectă este cu &#x219; și &#x21b; în loc de ş și ţ, dar este posibil ca aceste simboluri să nu fie afișate corect în browserul dumneavoastră.',
    'checked' => false, 
  ),
  'FORCE_DIACRITICS' => array(
    'value' => 1 << 1,
    'label' => 'Pun eu diacritice în căutare',
    'comment' => 'Fără această opțiune, o căutare după „mal” va returna și rezultatele pentru „mâl”. Cu această opțiune, rezultatele pentru „mâl” nu mai sunt returnate decât ' .
                 'când căutați explicit „mâl”.',
    'checked' => false, 
  ),
  'OLD_ORTHOGRAPHY' => array(
    'value' => 1 << 2,
    'label' => 'Folosesc ortografia folosită pînă în 1993 (î din i)',
    'comment' => 'Până în 1993, „&#xe2;” era folosit doar în cuvântul „român”, în cuvintele derivate și în unele nume proprii.',
    'checked' => false, 
  ),
  'EXCLUDE_UNOFFICIAL' => array(
  	'value' => 1 << 3,
	'label' => 'Vreau să vizualizez numai definițiile „oficiale”',
	'comment' => 'Sursele „neoficiale” nu au girul niciunei instituții acreditate de Academia Română sau a vreunei edituri de prestigiu', 
	'checked' => false,
  ),
  'SHOW_PARADIGM' => array(
  	'value' => 1 << 4,
	'label' => 'Doresc ca flexiunile să fie expandate',
	'comment' => 'Implicit, flexiunile sunt ascunse', 
	'checked' => false,
  ),
);

?>
