<?php

require_once __DIR__ . '/../phplib/util.php';

define('SERVER_URL', 'http://localhost/~cata/DEX/wwwbase');

$opts = getopt('', array('user:', 'source:', 'date:'));

if (count($opts) != 3) {
  usage();
}

$user = User::get_by_nick($opts['user']);
$source = Source::get_by_urlName($opts['source']);
$timestamp = strtotime($opts['date']);

if (!$user || !$source || !$timestamp) {
  usage();
}

$similarSource = SimilarSource::getSimilarSource($source->id);

if (!$similarSource) {
  usage();
}

$defs = Model::factory('Definition')
  ->where('userId', $user->id)
  ->where('sourceId', $source->id)
  ->where_gt('createDate', $timestamp)
  ->where('status', ST_ACTIVE)
  ->order_by_asc('lexicon')
  ->find_many();

$truePositives = $falsePositives = $trueNegatives = 0;

foreach ($defs as $def) {
  $lexemIds = db_getArray("select distinct lexemId from LexemDefinitionMap where definitionId = {$def->id}");
  $similar = $def->loadSimilar($lexemIds, $diffSize);
  if ($similar) {
    $correct = ($def->similarSource == 1) == ($diffSize == 0);
    if ($correct) {
      if ($def->similarSource) {
        $truePositives++;
      } else {
        $trueNegatives++;
      }
    } else {
      if ($def->similarSource) {
        $falsePositives++;
      } else {
	// Do not report false negatives; just fix them
        $correct = true;
        $def->similarSource = 1;
        $def->save();
      }
    }
    printf("Definiție [%s] bifă [%s] diferență %4d     %s URL %s/admin/definitionEdit?definitionId=%d\n",
           StringUtil::pad($def->lexicon, 20),
           ($def->similarSource ? 'X' : ' '),
           $diffSize,
           $correct ? '      ' : 'EROARE',
           SERVER_URL,
           $def->id);
  }
}

printf("True positives: %0.2lf%%\n", $truePositives / count($defs) * 100);
printf("True negatives: %0.2lf%%\n", $trueNegatives / count($defs) * 100);
printf("False positives: %0.2lf%%\n", $falsePositives / count($defs) * 100);


/*************************************************************************/

function usage() {
  print "Folosire: evalSimilarCheckbox --user <nick> --source <urlName> --date <YYYY-MM-DD>\n";
  print "Exemplu: evalSimilarCheckbox --user john --source dex09 --date 2014-01-01\n";
  print "Sursa trebuie să aibă o sursă similară aferentă.\n";
  exit(1);
}

?>
