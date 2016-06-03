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
$john = Model::factory('User')->create();
$john->email = 'john@x.com';
$john->nick = 'john';
$john->name = 'John Smith';
$john->save();

// sources
$klingon = Model::factory('Source')->create();
$klingon->shortName = 'Source 1';
$klingon->urlName = 'source1';
$klingon->name = 'English - Klingon Dictionary';
$klingon->author = 'Worf';
$klingon->publisher = 'The Klingon Academy';
$klingon->year = '2010';
$klingon->isOfficial = 2; // TODO add constants in Source.php
$klingon->displayOrder = 1;
$klingon->canContribute = 1;
$klingon->save();

$devil = Model::factory('Source')->create();
$devil->shortName = 'Source 2';
$devil->urlName = 'source2';
$devil->name = "The Devil's Dictionary";
$devil->author = 'Ambrose Bierce';
$devil->publisher = 'Neale Publishing Co.';
$devil->year = '1911';
$devil->isOfficial = 1;
$devil->displayOrder = 2;
$devil->save();

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

// inflection constraints
createConstraints('S', '%plural%', '%', -1);
createConstraints('W', '%vocativ, singular%', 'F', 1);
createConstraints('w', '%vocativ, singular%', 'F', 0);

// lexems
$l1 = createLexemDeep("br'ânză", 'F', '35', '', true);
$l2 = createLexemDeep("c'adă", 'F', '62', '', true);
$l3 = createLexemDeep("met'al", 'N', '1', '', true);
$l4 = createLexemDeep("d'in", 'T', '1', '', true);
$l5 = createLexemDeep("d'in", 'N', '1', '', true); // fictitious
$l6 = createLexemDeep("l'adă", 'F', '62', 'S', true);
$l7 = createLexemDeep("ogr'adă", 'F', '62', 'W', true);
$l1->frequency = 0.95; // for the Hangman game
$l1->save();

// definitions
$d1 = createDefinition(
  'Produs alimentar obținut prin coagularea și prelucrarea laptelui.',
  'brânză', $john->id, $klingon->id, Definition::ST_ACTIVE);
$d2 = createDefinition(
  'Recipient mare, deschis, din lemn, din metal, din beton etc.',
  'cadă', $john->id, $klingon->id, Definition::ST_ACTIVE);
$d3 = createDefinition(
  'prepoziție etc.',
  'din', $john->id, $klingon->id, Definition::ST_ACTIVE);
$d4 = createDefinition(
  'O dină, două dine, definiție fictivă pentru a avea lexeme omonime.',
  'din', $john->id, $klingon->id, Definition::ST_ACTIVE);

// lexem-definition maps
LexemDefinitionMap::associate($l1->id, $d1->id);
LexemDefinitionMap::associate($l2->id, $d2->id);
LexemDefinitionMap::associate($l4->id, $d3->id);
LexemDefinitionMap::associate($l5->id, $d4->id);

// comments
createComment('Foarte foarte gustoasă',
              $d1->id, $john->id, Definition::ST_ACTIVE);

// lexem sources
$ls = Model::factory('LexemSource')->create();
$ls->lexemId = $l3->id;
$ls->sourceId = $devil->id;
$ls->save();

// AdsLink
$al = Model::factory('AdsLink')->create();
$al->skey = 'wikipedia';
$al->name = 'wikipedia';
$al->url= 'http://wikipedia.org';
$al->save();

// WotD artists
$artist1 = createWotdArtist('artist1', 'Geniu Neînțeles', 'geniu@example.com', '© Geniu Neînțeles');
$artist2 = createWotdArtist('artist2', 'Luceafărul grafittiului românesc', 'luceafar@example.com', '© Luceafărul');

// Wiki articles, sections and keywords
$article1 = createWikiArticle(17, 123, 'Niciun sau nici un', 'Conținutul articolului 1.', 'Exprimare corectă');
$article2 = createWikiArticle(27, 345, 'Ghid de exprimare', 'Conținutul articolului 2.', null);
createWikiKeyword($article1->id, 'metal');
createWikiKeyword($article2->id, 'metal');
createWikiKeyword($article1->id, 'din');

// Tags
$tag1 = createTag('expresie', 0, 1);
$tag2 = createTag('registru stilistic', 0, 2);
$tag21 = createTag('argou', $tag2->id, 1);
$tag22 = createTag('familiar', $tag2->id, 2);

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

function createConstraints($code, $inflectionRegexp, $modelTypeRegexp, $variant) {
  $inflections = Model::factory('Inflection')
               ->where_like('description', $inflectionRegexp)
               ->where_like('modelType', $modelTypeRegexp)
               ->find_many();
  foreach ($inflections as $i) {
    $c = Model::factory('ConstraintMap')->create();
    $c->code = $code;
    $c->inflectionId = $i->id;
    $c->variant = $variant;
    $c->save();
  }
}

function createLexemDeep($form, $modelType, $modelNumber, $restriction, $isLoc) {
  $l = Lexem::create($form, $modelType, $modelNumber, $restriction, $isLoc);
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

function createComment($rep, $definitionId, $userId, $status) {
  $d = Definition::get_by_id($definitionId);

  $c = Model::factory('Comment')->create();
  $c->definitionId = $definitionId;
  $c->userId = $userId;
  $c->status = $status;
  $c->contents = $rep;
  $c->htmlContents = AdminStringUtil::htmlize($rep, $d->sourceId);
  $c->save();
  return $c;
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

function createWikiArticle($pageId, $revId, $title, $body, $section) {
  $a = Model::factory('WikiArticle')->create();
  $a->pageId = $pageId;
  $a->revId = $revId;
  $a->title = $title;
  $a->fullUrl = '';
  $a->wikiContents = $body;
  $a->htmlContents = $body;
  $a->save();

  if ($section) {
    $s = Model::factory('WikiSection')->create();
    $s->pageId = $pageId;
    $s->section = $section;
    $s->save();
  }

  return $a;
}

function createWikiKeyword($wikiArticleId, $keyword) {
  $wk = Model::factory('WikiKeyword')->create();
  $wk->wikiArticleId = $wikiArticleId;
  $wk->keyword = $keyword;
  $wk->save();
}

function createTag($value, $parentId, $displayOrder) {
  $t = Model::factory('Tag')->create();
  $t->value = $value;
  $t->parentId = $parentId;
  $t->displayOrder = $displayOrder;
  $t->save();
  return $t;
}
