<?php

require_once __DIR__ . '/../phplib/util.php';

// Make sure we are in testing mode.
Config::get('testing.enabled')
  or die("Please set enabled = true in the [testing] section.\n");

// Make sure we are in development mode. We need fake logins.
Config::get('global.developmentMode')
  or die("Please set developmentMode = 1 in the [global] section.\n");

// Drop and recreate the testing DB.
// Execute this at PDO level, since idiorm cannot connect to a non-existing DB.
$gdsn = db_splitDsn(Config::get('general.database'));
$tdsn = db_splitDsn(Config::get('testing.database'));

$pdo = new PDO('mysql:host=' . $tdsn['host'], $tdsn['user'], $tdsn['password']);
$pdo->query('drop database if exists ' . $tdsn['database']);
$pdo->query('create database if not exists ' . $tdsn['database']);

// Warning about passwords on command line.
if ($gdsn['password'] || $tdsn['password']) {
  print "This script needs to run some mysqldump and mysql shell commands.\n";
  print "However, your DB DSN includes a password. We cannot add plaintext passwords\n";
  print "to MySQL commands. Please specify your username/password in ~/.my.cnf like so:\n";
  print "\n";
  print "[client]\n";
  print "user=your_username\n";
  print "password=your_password\n";
}

// Copy the schema from the regular DB.
// Use sed to remove AUTO_INCREMENT values - we want to start at 1.
exec(sprintf('mysqldump -h %s -u %s %s -d | sed -e "s/AUTO_INCREMENT=[[:digit:]]* //" | mysql -h %s -u %s %s',
             $gdsn['host'], $gdsn['user'], $gdsn['database'],
             $tdsn['host'], $tdsn['user'], $tdsn['database']));

// Create some data.

// users
$u = Model::factory('User')->create();
$u->email = 'john@x.com';
$u->nick = 'john';
$u->name = 'John Smith';
$u->save();

// sources
$s = Model::factory('Source')->create();
$s->shortName = 'Source 1';
$s->urlName = 'source1';
$s->name = 'English - Klingon Dictionary';
$s->author = 'Worf';
$s->publisher = 'The Klingon Academy';
$s->year = '2010';
$s->isOfficial = 2; // TODO add constants in Source.php
$s->displayOrder = 1;
$s->save();

$s = Model::factory('Source')->create();
$s->shortName = 'Source 2';
$s->urlName = 'source2';
$s->name = "The Devil's Dictionary";
$s->author = 'Ambrose Bierce';
$s->publisher = 'Neale Publishing Co.';
$s->year = '1911';
$s->isOfficial = 1;
$s->displayOrder = 2;
$s->save();

// model types
createModelType('T', 'T', 'temporar');
createModelType('F', 'F', 'substantiv feminin');
createModelType('AF', 'F', 'adjectiv feminin');
createModelType('N', 'N', 'substantiv neutru');

// inflections
createInflections('T', [
  'formă unică',
]);
$descriptions = [
  'nominativ, singular, nearticulat',
  'genitiv, singular, nearticulat',
  'nominativ, plural, nearticulat',
  'genitiv, plural, nearticulat',
  'nominativ, singular, articulat',
  'genitiv, singular, articulat',
  'nominativ, plural, articulat',
  'genitiv, plural, articulat',
  'vocativ, singular',
  'vocativ, plural',
]; // reuse these
createInflections('F', $descriptions);
createInflections('N', $descriptions);

// models, transforms and model descriptions
createModelDeep('T', '1', '', 'invariabil', [
  [ 'invariabil' ],
]);
createModelDeep('F', '35', '+et', "br'ânză", [
  [ "br'ânză" ],
  [ "br'ânze" ],
  [ "brânz'eturi" ],
  [ "brânz'eturi" ],
  [ "br'ânza" ],
  [ "br'ânzei" ],
  [ "brânz'eturile" ],
  [ "brânz'eturilor" ],
  [ "br'ânză", "br'ânzo" ],
  [ "brânz'eturilor" ],
]);
createModelDeep('F', '62', '', "str'adă", [
  [ "str'adă" ],
  [ "str'ăzi" ],
  [ "str'ăzi" ],
  [ "str'ăzi" ],
  [ "str'ada" ],
  [ "str'ăzii" ],
  [ "str'ăzile" ],
  [ "str'ăzilor" ],
  [ "str'adă", "str'ado" ],
  [ "str'ăzilor" ],
]);
createModelDeep('N', '1', '', "f'ir", [
  [ "f'ir" ],
  [ "f'ir" ],
  [ "f'ire" ],
  [ "f'ire" ],
  [ "f'irul" ],
  [ "f'irului" ],
  [ "f'irele" ],
  [ "f'irelor" ],
  [ "f'irule", "f'ire" ],
  [ "f'irelor" ],
]);

// lexems
$l1 = createLexemDeep("br'ânză", 'F', '35', '', true);
$l2 = createLexemDeep("c'adă", 'F', '62', '', true);
$l3 = createLexemDeep("met'al", 'N', '1', '', true);
$l4 = createLexemDeep("d'in", 'T', '1', '', true);
$l5 = createLexemDeep("d'in", 'N', '1', '', true); // fictitious

// definitions
$d1 = createDefinition(
  'Produs alimentar obținut prin coagularea și prelucrarea laptelui.',
  'brânză', 1, 1, Definition::ST_ACTIVE);
$d2 = createDefinition(
  'Recipient mare, deschis, din lemn, din metal, din beton etc.',
  'cadă', 1, 1, Definition::ST_ACTIVE);
$d3 = createDefinition(
  'prepoziție etc.',
  'din', 1, 1, Definition::ST_ACTIVE);
$d4 = createDefinition(
  'O dină, două dine, definiție fictivă pentru a avea lexeme omonime.',
  'din', 1, 1, Definition::ST_ACTIVE);

// lexem-definition maps
LexemDefinitionMap::associate($l1->id, $d1->id);
LexemDefinitionMap::associate($l2->id, $d2->id);
LexemDefinitionMap::associate($l4->id, $d3->id);
LexemDefinitionMap::associate($l5->id, $d4->id);

// AdsLink
$al = Model::factory('AdsLink')->create();
$al->skey = 'wikipedia';
$al->name = 'wikipedia';
$al->url= 'http://wikipedia.org';
$al->save();

// WotD artists
$artist1 = createWotdArtist('artist1', 'Geniu Neînțeles', 'geniu@example.com', '© Geniu Neînțeles');
$artist2 = createWotdArtist('artist2', 'Luceafărul grafittiului românesc', 'luceafar@example.com', '© Luceafărul');

// run some preprocessing
require_once __DIR__ . '/../tools/genNGram.php';
require_once __DIR__ . '/../tools/rebuildAutocomplete.php';
require_once __DIR__ . '/../tools/rebuildFullTextIndex.php';

/**************************************************************************/

function createModelType($code, $canonical, $description) {
  $mt = Model::factory('ModelType')->create();
  $mt->code = $code;
  $mt->description = $description;
  $mt->canonical = $canonical;
  $mt->save();
}

function createInflections($modelType, $descriptions) {
  foreach ($descriptions as $i => $d) {
    $infl = Model::factory('Inflection')->create();
    $infl->description = $d;
    $infl->modelType = $modelType;
    $infl->rank = $i + 1;
    $infl->save();
  }
}

function createModelDeep($type, $number, $description, $exponent, $paradigm) {
  $m = Model::factory('FlexModel')->create();
  $m->modelType = $type;
  $m->number = $number;
  $m->description = $description;
  $m->exponent = $exponent;
  $m->save();

  foreach ($paradigm as $i => $forms) {
    $infl = Inflection::get_by_modelType_rank($type, $i + 1);

    foreach ($forms as $variant => $form) {
      $transforms = FlexStringUtil::extractTransforms($m->exponent, $form, false);

      $accentShift = array_pop($transforms);
      if ($accentShift != UNKNOWN_ACCENT_SHIFT &&
          $accentShift != NO_ACCENT_SHIFT) {
        $accentedVowel = array_pop($transforms);
      } else {
        $accentedVowel = '';
      }
    
      $order = count($transforms);
      foreach ($transforms as $t) {
        $t = Transform::createOrLoad($t->transfFrom, $t->transfTo);
        $md = Model::factory('ModelDescription')->create();
        $md->modelId = $m->id;
        $md->inflectionId = $infl->id;
        $md->variant = $variant;
        $md->applOrder = --$order;
        $md->transformId = $t->id;
        $md->accentShift = $accentShift;
        $md->vowel = $accentedVowel;
        $md->isLoc = true;
        $md->recommended = true;
        $md->save();
      }
    }
  }
}

function createLexemDeep($form, $modelType, $modelNumber, $restriction, $isLoc) {
  $l = Lexem::deepCreate($form, $modelType, $modelNumber, $restriction, $isLoc);
  $l->deepSave();
  return $l;
}

function createDefinition($rep, $lexicon, $userId, $sourceId, $status) {
  $d = Model::factory('Definition')->create();
  $d->userId = $userId;
  $d->sourceId = $sourceId;
  $d->lexicon = $lexicon;
  $d->internalRep = $rep;
  $d->htmlRep = AdminStringUtil::htmlize($rep, $sourceId);
  $d->status = $status;
  $d->save();
  return $d;
}

function createWotdArtist($label, $name, $email, $credits) {
  $a = Model::factory('WotdArtist')->create();
  $a->label = $label;
  $a->name = $name;
  $a->email = $email;
  $a->credits = $credits;
  $a->save();
  return $a;
}
