<?
// Expected: 257 models. Model 666 appears twice, but one of the instances has
// "display: none". Also, note models 657 and 657'.
require_once("common.php");

DEFINE('FILE_NAME', '/tmp/loc4-v.html');
DEFINE('EXPECTED_MODELS', 257);

$GLOBALS['pronouns'] = array('', 'eu', 'tu', 'el', 'noi', 'voi', 'ei');

DEFINE('INFL_INFINITIVE', 49);
DEFINE('INFL_LONG_INFINITIVE', 50);
DEFINE('INFL_IMPERATIVE', 51);
DEFINE('INFL_PARTICIPLE', 52);
DEFINE('INFL_GERUND', 53);
DEFINE('INFL_PRESENT', 53); // + person
DEFINE('INFL_SUBJONCTIVE', 59); // + person
DEFINE('INFL_IMPERFECT', 65); // + person
DEFINE('INFL_PERFECT_SIMPLE', 71); // + person
DEFINE('INFL_PAST_PERFECT', 77); // + person

DEFINE('ST_MODEL_NUMBER', 0);
DEFINE('ST_INFINITIVE', 1);
DEFINE('ST_LONG_INFINITIVE', 2);
DEFINE('ST_IMPERATIVE', 3);
DEFINE('ST_SLAVE_MODELS', 4);
DEFINE('ST_PARTICIPLE', 5);
DEFINE('ST_GERUND', 6);
DEFINE('ST_PARTICIPLE_MODEL', 7);
DEFINE('ST_TENSE_NAMES', 8);
DEFINE('ST_5_TENSES', 9);
// A few models (93.) list four of the tenses first and the past perfect next.
DEFINE('ST_4_TENSES', 10);
DEFINE('ST_FINAL', 11);

DEFINE('REG_MODEL_NUMBER',
       '/<td class="xl(31|50)" style="height: 12pt;" height="16">([0-9\']+)\.<\/td>/');
DEFINE('REG_INFINITIVE',
       '/<td [^>]*>([^<]+)<\/td>/');
DEFINE('REG_LONG_INFINITIVE',
       '/<td[^>]*>infinitiv lung :<\/td>[^<]*<td[^>]*>([^<]*)<\/td>/');
DEFINE('REG_IMPERATIVE',
       '/<td[^>]*>imperativ pers\. 2 :<\/td>[^<]*<td[^>]*>([^<]*)<\/td>/');
DEFINE('REG_SLAVE_MODELS',
       '/<tr[^>]*>[^<]*<td[^>]*>([^<]*)<\/td>/');
DEFINE('REG_PARTICIPLE',
       '/<td[^>]*>participiu :<\/td>[^<]*<td[^>]*>([^<]*)<\/td>/');
DEFINE('REG_GERUND',
       '/<td[^>]*>gerunziu :<\/td>[^<]*<td[^>]*>([^<]*)<\/td>/');
DEFINE('REG_PARTICIPLE_MODEL',
       '/<td[^>]*>tip de declinare: a (\d+)<\/td>/');

list($verbose, $fileName) = parseArguments();
$data = readAndFormatFile($fileName);

$pos = 0;
$state = ST_MODEL_NUMBER;
$matches = array();
$done = false;
$numModels = 0;
$modelMap = array();

$modelNumber = 0;
$infinitive = '';
$longInfinitive = '';
$imperative = '';
$slaveModels = false;
$participle = '';
$gerund = '';
$participleModel = '';
$present = array();
$subjonctive = array();
$imperfect = array();
$perfectSimple = array();
$pastPerfect = array();

while (!$done) {
  switch ($state) {
  case ST_MODEL_NUMBER:
    if (matchRegexp(REG_MODEL_NUMBER)) {
      $modelNumber = $matches[2][0];
      addToModelMap($modelMap, $modelNumber);
      $numModels++;
      $present = array();
      $subjonctive = array();
      $imperfect = array();
      $perfectSimple = array();
      $pastPerfect = array();
      $state = ST_INFINITIVE;
      dprint("");
      dprint("Beginning of model $modelNumber");
    } else {
      $done = true;
    }
    break;

  case ST_INFINITIVE:
    assert(matchRegexp(REG_INFINITIVE));
    $infinitive = $matches[1][0];
    dprint("Infinitive: $infinitive");
    $state = ST_LONG_INFINITIVE;
    break;

  case ST_LONG_INFINITIVE:
    assert(matchRegexp(REG_LONG_INFINITIVE));
    $longInfinitive = $matches[1][0];
    dprint("Long infinitive: $longInfinitive");
    $state = ST_IMPERATIVE;
    break;

  case ST_IMPERATIVE:
    assert(matchRegexp(REG_IMPERATIVE));
    $imperative = $matches[1][0];
    dprint("Imperativ: $imperative");
    $state = ST_SLAVE_MODELS;
    break;

  case ST_SLAVE_MODELS:
    assert(matchRegexp(REG_SLAVE_MODELS));
    $slaveModels = parseSlaveModels($matches[1][0]);
    if (count($slaveModels)) {
      foreach ($slaveModels as $sm) {
        addToModelMap($modelMap, $sm);
      }
      dprint("$modelNumber Slave models: " . join(',', $slaveModels));
    }
    $state = ST_PARTICIPLE;
    break;

  case ST_PARTICIPLE:
    assert(matchRegexp(REG_PARTICIPLE));
    $participle = $matches[1][0];
    dprint("Participle: $participle");
    $state = ST_GERUND;
    break;

  case ST_GERUND:
    assert(matchRegexp(REG_GERUND));
    $gerund = $matches[1][0];
    dprint("Gerund: $gerund");
    $state = ST_PARTICIPLE_MODEL;
    break;

  case ST_PARTICIPLE_MODEL:
    // This is a bit tricky since the participle model is optional.
    // If that is the case, we will either have a match from a future model
    // or no match at all.
    $futureModel = testRegexp(REG_MODEL_NUMBER);
    $startFutureModel = $futureModel ? $matches[0][1] : 0;
    $someClause = testRegexp(REG_PARTICIPLE_MODEL);
    $startClause = $someClause ? $matches[0][1] : 0;

    if ($someClause && (!$futureModel || $startClause < $startFutureModel)) {
      $participleModel = $matches[1][0];
      $pos = $matches[0][1] + strlen($matches[0][0]);
      dprint("Participle model: $participleModel");
    } else {
      // The default model is A2.
      $participleModel = 2;
    }
    $state = ST_TENSE_NAMES;
    break;

  case ST_TENSE_NAMES:
    do {
      $cells = captureTr();
    }
    while (!count($cells));
    assert(count($cells) == 4 || count($cells == 5));
    if (count($cells) == 5) {
      $state = ST_5_TENSES;
    } else {
      $state = ST_4_TENSES;
    }
    break;

  case ST_5_TENSES:
    $cells = captureTr(); // skip an empty row;
    assert(!count($cells));
    while (($cells = captureTr()) && count($cells) == 8) {
      // The eight cells represent:
      // 1. eu jur să jur juram jurai jurasem
      // (person) (pronoun) (present) (să) (subjonctive) (imperfect)
      // (perfect simple) (past perfect)
      assert(strlen($cells[0]) == 2 && $cells[0][1] == '.');
      $person = $cells[0][0];
      assert($cells[1] == $GLOBALS['pronouns'][$person]);
      $present[$person] = $cells[2];
      assert($cells[3] == 'să');
      $subjonctive[$person] = $cells[4];
      $imperfect[$person] = $cells[5];
      $perfectSimple[$person] = $cells[6];
      $pastPerfect[$person] = $cells[7];
    }
    $state = ST_FINAL;
    break;

  case ST_4_TENSES:
    $cells = captureTr(); // skip an empty row;
    assert(!count($cells));
    while (($cells = captureTr()) && count($cells) == 7) {
      // The seven cells represent:
      // 1. eu dau să dau dădeam dădui
      // (person) (pronoun) (present) (să) (subjonctive) (imperfect)
      // (perfect simple)
      assert(strlen($cells[0]) == 2 && $cells[0][1] == '.');
      $person = $cells[0][0];
      assert($cells[1] == $GLOBALS['pronouns'][$person]);
      $present[$person] = $cells[2];
      assert($cells[3] == 'să');
      $subjonctive[$person] = $cells[4];
      $imperfect[$person] = $cells[5];
      $perfectSimple[$person] = $cells[6];
    }
    assert(count($cells) == 3);
    assert($cells[0] == 'm. m. c. perfect:');
    $cells = array_slice($cells, 1);

    while (count($cells) == 2) {
      foreach ($cells as $cell) {
	assert($cell[1] == '.' && $cell[2] == ' ');
	$person = $cell[0];
	$pastPerfect[$person] = substr($cell, 3);
      }
      $cells = captureTr();
    }
    ksort($pastPerfect);
    $state = ST_FINAL;
    break;

  case ST_FINAL:
    dprintArray($present, 'Present');
    dprintArray($subjonctive, 'Subjonctive');
    dprintArray($imperfect, 'Imperfect');
    dprintArray($perfectSimple, 'Perfect simple');
    dprintArray($pastPerfect, 'Past perfect');

    $personCount = count($present);
    assert($personCount == 2 || $personCount == 6);
    assert(count($subjonctive) == $personCount);
    assert(count($imperfect) == $personCount);
    assert(count($perfectSimple) == $personCount);
    assert(count($pastPerfect) == $personCount);

    saveModel($modelNumber, $infinitive, $longInfinitive, $imperative,
	      $slaveModels, $participle, $gerund, $participleModel, $present,
	      $subjonctive, $imperfect, $perfectSimple, $pastPerfect);
    
    $state = ST_MODEL_NUMBER;
  }
}

analyzeModelMap($modelMap);
assertEquals(EXPECTED_MODELS, $numModels);

/*************************************************************************/

function parseSlaveModels($str) {
  $a = array();
  $str = str_replace(array('(*)', '([*])', '.'), array('', '', ''), $str);
  $parts = split(',', $str);
  foreach ($parts as $part) {
    $trimmed = trim($part);
    if ($trimmed) {
      $a[] = $trimmed;
    }
  }
  return $a;
}

function addToModelMap(&$modelMap, $modelNumber) {
  assert(!array_key_exists($modelNumber, $modelMap));
  $modelMap[$modelNumber] = 1;
}

function analyzeModelMap($map) {
  print "Contiguous numbering intervals:";

  ksort($map);
  $start = 0;
  $previous = 0;
  foreach ($map as $key => $ignored) {
    if (!$start) {
      $start = $key;
    } else if ($key == "657'" || $key == 658 || $key == $previous + 1) {
      // Do nothing, the interval is still contiguous
    } else {
      print " [$start-$previous]";
      $start = $key;
    }
    $previous = $key;
  }
  print " [$start-$previous]\n";
}

function saveModel($modelNumber, $infinitive, $longInfinitive, $imperative,
                   $slaveModels, $participle, $gerund, $participleModel,
                   $present, $subjonctive, $imperfect, $perfectSimple,
                   $pastPerfect) {
  $forms = array();
  $inflections = array();

  $forms[] = $infinitive; $inflections[] = INFL_INFINITIVE;
  $forms[] = $longInfinitive; $inflections[] = INFL_LONG_INFINITIVE;
  $forms[] = $imperative; $inflections[] = INFL_IMPERATIVE;
  $forms[] = $participle; $inflections[] = INFL_PARTICIPLE;
  $forms[] = $gerund; $inflections[] = INFL_GERUND;
  addPersonalForms($forms, $inflections, $present, INFL_PRESENT);
  addPersonalForms($forms, $inflections, $subjonctive, INFL_SUBJONCTIVE);
  addPersonalForms($forms, $inflections, $imperfect, INFL_IMPERFECT);
  addPersonalForms($forms, $inflections, $perfectSimple, INFL_PERFECT_SIMPLE);
  addPersonalForms($forms, $inflections, $pastPerfect, INFL_PAST_PERFECT);
  saveCommonModel('V', $modelNumber, $forms, '', $inflections);

  // Add the mapping from this verb model to the corresponding adjective model
  $pm = ParticipleModel::create($modelNumber, $participleModel);
  $pm->save();

  foreach ($slaveModels as $sm) {
    $mm = ModelMapping::create('V', $sm, $modelNumber);
    $mm->save();
  }
}

function addPersonalForms(&$forms, &$inflections, $formSet, $baseInflId) {
  foreach ($formSet as $person => $form) {
    $forms[] = $form;
    $inflections[] = $baseInflId + $person;
  }
}

?>
