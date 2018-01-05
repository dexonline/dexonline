<?php
/**
 * Confront genus / species names with data from theplantlist.org.
 **/

require_once __DIR__ . '/../phplib/Core.php';

define('SOURCE_ID', 63);
define('WITNESS_CSV', '/tmp/species.csv');
define('WITNESS_JSON', '/tmp/species.json');

$canonicalGenus = [
  'Anigosanthus' => 'Anigozanthos',
  'Cajophora' => 'Caiophora',
  'Chamaelaucium' => 'Chamelaucium',
  'Chondrorrhyncha' => 'Chondrorhyncha',
  'Coryphanta' => 'Coryphantha',
  'Desfontainea' => 'Desfontainia',
  'Eritrichum' => 'Eritrichium',
  'Geissorrhiza' => 'Geissorhiza',
  'Gleditschia' => 'Gleditsia',
  'Hebenstreitia' => 'Hebenstretia',
  'Helicrysum' => 'Helichrysum',
  'Hydrocleis' => 'Hydrocleys',
  'Ichnosiphon' => 'Ischnosiphon',
  'Jochroma' => 'Iochroma',
  'Jonopsis' => 'Ionopsis',
  'Kaempfera' => 'Kaempferia',
  'Lapeyrousia' => 'Lapeirousia',
  'Laya' => 'Layia',
  'Majanthemum' => 'Maianthemum',
  'Matteucia' => 'Matteuccia',
  'Maurandia' => 'Maurandya',
  'Ostrowskya' => 'Ostrowskia',
  'Pitcairnea' => 'Pitcairnia',
  'Raphiolepis' => 'Rhaphiolepis',
  'Sabbatia' => 'Sabatia',
  'Symphoricarpus' => 'Symphoricarpos',
  'Zygopetalon' => 'Zygopetalum',
];

// genJson();
$witness = loadJson();  // $witness[$genus][$species] => true

$scount = []; // $scount[$key] = number of occurrences of species under all genera
foreach ($witness as $speciesMap) {
  foreach ($speciesMap as $key => $ignored) {
    $value = $scount[$key] ?? 0;
    $scount[$key] = 1 + $value;
  }
}

$defs = Model::factory('Definition')
      ->where('sourceId', SOURCE_ID)
      ->where('status', Definition::ST_ACTIVE)
      ->order_by_asc('lexicon')
      ->find_many();

foreach ($defs as $d) {
  
  if (preg_match('/^@([A-ZÉË]+)@ /', $d->internalRep, $matches)) {
    // Genus
    $genus = ucfirst(mb_strtolower($matches[1]));
    $species = null;
    printf("%s gen %s\n", $matches[0], $genus);
  } else if (preg_match('/^@([A-Z][a-zéë]+) (x |var\. )?([-a-zë]+)@/', $d->internalRep, $matches)) {
    // Genus species
    $genus = $matches[1];
    $species = $matches[3];
    printf("%s gen %s specie %s\n", $matches[0], $genus, $species);
  } else {
    $genus = null;
    $species = null;
    printf("Nu pot deduce numele speciei: [%s] %s%d\n",
           mb_substr($d->internalRep, 0, 80),
           "https://dexonline.ro/admin/definitionEdit.php?definitionId=",
           $d->id);
  }

  if ($genus) {
    $genus = StringUtil::unicodeToLatin($genus);
    $genus = $canonicalGenus[$genus] ?? $genus;
    if (!isset($witness[$genus])) {
      printf("Nu recunosc genul: [%s %s] %s%d\n",
             $genus, $species ?? '*',
             "https://dexonline.ro/admin/definitionEdit.php?definitionId=",
             $d->id);
    }
  }

  if ($genus && $species) {
    $species = StringUtil::unicodeToLatin($species);

    if (!isset($witness[$genus][$species])) {
      $count = $scount[$species] ?? 0;
      printf("Nu recunosc specia: [%s %s]%s %s%d %s%%22%s+%s%%22\n",
             $genus, $species,
             $count ? " ({$count} mențiuni în alte genuri)" : '',
             "https://dexonline.ro/admin/definitionEdit.php?definitionId=",
             $d->id,
             "https://www.google.com/search?q=",
             $genus, $species);
    }
  }
}

/*************************************************************************/

function genJson() {
  $f = @fopen(WITNESS_CSV, 'r') or die("Nu pot deschide fișierul-martor.\n");
  $witness = [];
  while (($row = fgetcsv($f, 1000, ",")) !== false) {
    $witness[$row[4]][$row[6]] = true;
  }
  fclose($f);
  file_put_contents(WITNESS_JSON, json_encode($witness));
}

function loadJson() {
  return json_decode(file_get_contents(WITNESS_JSON), true);
}
