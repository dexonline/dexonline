<?php

/**
 * Split MF entries / lexemes into M / F / A
 **/

require_once __DIR__ . '/../phplib/Core.php';

const START_AT = '';
const FEMININE_INFLECTION_ID = 33;
const LONG_INFINITIVE_MODEL_IDS = [ 284, 290 ]; // F107 and F113

// MF model => [ M model, list of F models ]
const TYPE_MAP = [
  1 => [ 1, [ 1 ] ],
  2 => [ 3, [ 1 ] ],
  3 => [ 5, [ 1 ] ],
  4 => [ 6, [ 1 ] ],
  5 => [ 8, [ 1 ] ],
  6 => [ 9, [ 1 ] ],
  7 => [ 8, [ 1 ] ],
  8 => [ 10, [ 1 ] ],
  9 => [ 12, [ 1 ] ],
  10 => [ 13, [ 4 ] ],
  11 => [ 14, [ 6 ] ],
  12 => [ 16, [ 7 ] ],
  13 => [ 17, [ 8 ] ],
  15 => [ 1, [ 10 ] ],
  16 => [ 1, [ 10 ] ],
  17 => [ 20, [ 12 ] ],
  18 => [ 1, [ 12 ] ],
  19 => [ 21, [ 15 ] ],
  20 => [ 22, [ 16 ] ],
  21 => [ 23, [ 17 ] ],
  22 => [ 1, [ 17 ] ],
  23 => [ 24, [ 17 ] ],
  24 => [ 24, [ 16 ] ],
  25 => [ 27, [ 21 ] ],
  26 => [ 26, [ 12 ] ],
  27 => [ 1, [ 1 ] ],
  28 => [ 1, [ 1 ] ],
  30 => [ 1, [ 1 ] ],
  34 => [ 3, [ 12 ] ],
  35 => [ 31, [ 12 ] ],
  37 => [ 3, [ 12 ] ],
  38 => [ 3, [ 17 ] ],
  39 => [ 3, [ 1 ] ],
  40 => [ 34, [ 21 ] ],
  42 => [ 35, [ 22 ] ],
  43 => [ 3, [ 1 ] ],
  44 => [ 5, [ 1 ] ],
  45 => [ 3, [ 1 ] ],
  48 => [ 6, [ 12 ] ],
  49 => [ 6, [ 15 ] ],
  50 => [ 6, [ 16 ] ],
  51 => [ 6, [ 1 ] ],
  52 => [ 9, [ 1 ] ],
  53 => [ 36, [ 12 ] ],
  54 => [ 12, [ 16 ] ],
  55 => [ 12, [ 1 ] ],
  56 => [ 13, [ 26 ] ],
  57 => [ 37, [ 26 ] ],
  58 => [ 14, [ 27 ] ],
  59 => [ 38, [ 27 ] ],
  60 => [ 37, [ 29 ] ],
  61 => [ 38, [ 29 ] ],
  62 => [ 39, [ 30 ] ],
  63 => [ 13, [ 4 ] ],
  64 => [ 14, [ 6 ] ],
  66 => [ 1, [ 103 ] ],
  67 => [ 12, [ 151 ] ],
  68 => [ 12, [ 153 ] ],
  69 => [ 12, [ 154 ] ],
  70 => [ 12, [ 39 ] ],
  71 => [ 12, [ 151, 39 ] ],
  72 => [ 12, [ 153, 39 ] ],
  73 => [ 12, [ 154, 39 ] ],
  74 => [ 12, [ 155, 39 ] ],
  75 => [ 13, [ 46 ] ],
  76 => [ 14, [ 47 ] ],
  77 => [ 14, [ 47 ] ],
  78 => [ 17, [ 49 ] ],
  79 => [ 13, [ 76 ] ],
  80 => [ 14, [ 77 ] ],
  81 => [ 17, [ 86 ] ],
  82 => [ 17, [ 86 ] ],
  83 => [ 17, [ 86 ] ],
  84 => [ 17, [ 91 ] ],
  86 => [ 46, [ 1 ] ],
  87 => [ 45, [ 1 ] ],
  88 => [ 51, [ 104 ] ],
  89 => [ 45, [ 107 ] ],
  90 => [ 51, [ 122 ] ],
  91 => [ 46, [ 109 ] ],
  92 => [ 47, [ 110 ] ],
  96 => [ 62, [ 1 ] ],
  97 => [ 63, [ 1 ] ],
  98 => [ 62, [ 12 ] ],
  99 => [ 62, [ 1 ] ],
  100 => [ 63, [ 17 ] ],
  102 => [ 69, [ 129 ] ],
  103 => [ 69, [ 142 ] ],
  104 => [ 69, [ 151 ] ],
  105 => [ 69, [ 151 ] ],
  106 => [ 69, [ 1 ] ],
  107 => [ 69, [ 1 ] ],
  108 => [ 69, [ 134 ] ],
  109 => [ 69, [ 135 ] ],
  110 => [ 69, [ 135 ] ],
  111 => [ 69, [ 130 ] ],
  114 => [ 69, [ 131 ] ],
  115 => [ 73, [ 104 ] ],
  116 => [ 73, [ 104 ] ],
  117 => [ 73, [ 122 ] ],
  120 => [ 78, [ 129 ] ],
  121 => [ 78, [ 129 ] ],
  122 => [ 78, [ 130 ] ],
  125 => [ 999, [ 999 ] ],
];

$tags = [
  'MF' => Tag::get_by_value('substantiv masculin și feminin'),
  'M' => Tag::get_by_value('substantiv masculin'),
  'F' => Tag::get_by_value('substantiv feminin'),
  'A' => Tag::get_by_value('adjectiv'),
];

// load entries having lexemes of types 'MF' or 'A'
$entries = Model::factory('Entry')
         ->table_alias('e')
         ->select('e.*')
         ->distinct()
         ->join('EntryLexem', ['e.id', '=', 'el.entryId'], 'el')
         ->join('Lexem', ['el.lexemId', '=', 'l.id'], 'l')
         ->where_in('l.modelType', [ 'MF', 'A' ])
         ->where_gt('e.description', START_AT)
         ->order_by_asc('e.description')
         ->find_many();

foreach ($entries as $e) {
  // Split the entry if it has any noun-type lexemes. This is OK to do, because either it
  // has an MF lexeme or it has an A lexeme (per the above query) and a noun lexeme.
  // Skip long infinitive lexems, which indicate a verb + participle + long infinitive entry.
  $toSplit = Model::factory('Lexem')
           ->table_alias('l') 
           ->join('EntryLexem', ['l.id', '=', 'el.lexemId'], 'el')
           ->join('ModelType', ['l.modelType', '=',  'mt.code'], 'mt')
           ->join('Model', 'mt.canonical = m.modelType and l.modelNumber = m.number', 'm')
           ->where('el.entryId', $e->id)
           ->where_in('l.modelType', [ 'MF', 'M', 'F' ])
           ->where_not_in('m.id', LONG_INFINITIVE_MODEL_IDS)
           ->count();


  // Also split the entry if any of its lexemes have a noun tag
  if (!$toSplit) {
    $toSplit = Model::factory('Lexem')
             ->table_alias('l') 
             ->join('EntryLexem', ['l.id', '=', 'el.lexemId'], 'el')
             ->join('ObjectTag', ['ot.objectId', '=', 'l.id'], 'ot')
             ->where('el.entryId', $e->id)
             ->where('ot.objectType', ObjectTag::TYPE_LEXEM)
             ->where_in('ot.tagId', [$tags['MF']->id, $tags['M']->id, $tags['F']->id])
             ->count();
  }

  // However, don't split it if any A / MF lexemes have model numbers we don't know how to split
  if ($toSplit) {
    foreach ($e->getLexems() as $l) {
      if (in_array($l->modelType, ['A', 'MF']) &&
          !isset(TYPE_MAP[$l->modelNumber])) {
        printf("==== Sar peste intrarea [$e] deoarece nu știu să sparg modelul " .
               "{$l->modelType}{$l->modelNumber}\n");
        $toSplit = false;
      }
    }
  }
  continue;

  if ($toSplit) {

    printf("==== procesez intrarea [$e]\n");

    $femForm = getFeminineForm($e);

    if (!$femForm) {
      printf("  * EROARE: nu pot deduce forma de feminin pentru [{$e}](ID intrare={$e->id})\n");
    } else {
      list($em, $ef, $ea) = createEntries($e, $femForm);
      assignLexemes($em, $ef, $ea, $e->getLexems());
      printf("  * șterg intrarea originală\n");
      $e->delete();
    }

  } else {
    // printf("skipping $e\n");
  }
  
}

/*************************************************************************/

function createEntries($e, $femForm) {
  global $tags;

  $em = $ef = $ea = null;

  // collect model types from the lexeme modelType and from relevant tags
  $modelTypes = [];
  foreach ($e->getLexems() as $l) {
    $modelTypes[$l->modelType] = true;
    foreach ($tags as $modelType => $ignored) {
      if (hasTag($l, $modelType)) {
        $modelTypes[$modelType] = true;
      }
    }
  }

  // create entries
  if (isset($modelTypes['MF']) || isset($modelTypes['M'])) {
    printf("  * creez intrarea pentru s.m.: [{$e->description}]\n");
    $em = $e->_clone(true, false, true, true); // do not clone lexeme associations
  }
  if (isset($modelTypes['MF']) || isset($modelTypes['F'])) {
    $shortDesc = $e->getShortDescription();
    $femDesc = str_replace($shortDesc, $femForm, $e->description);
    printf("  * creez intrarea pentru s.f.: [{$femDesc}]\n");
    $ef = $e->_clone(true, false, true, true);
    $ef->description = $femDesc;
    $ef->save();
  }
  if (isset($modelTypes['A'])) {
    printf("  * creez intrarea pentru adj.: [{$e->description}]\n");
    $ea = $e->_clone(true, false, true, true);
  }

  // disambiguate the masculine and adjective entries if both exist
  if ($em && $ea) {
    if (!preg_match('/s\. ?m\./', $em->description)) {
      $newDesc = $em->description . ' (s.m.)';
      printf("  * dezambiguizez intrarea pentru s.m.: [{$newDesc}]\n");
      $em->description = $newDesc;
      $em->save();
    }
    if (!preg_match('/adj\./', $ea->description)) {
      $newDesc = $ea->description . ' (adj.)';
      printf("  * dezambiguizez intrarea pentru adj.: [{$newDesc}]\n");
      $ea->description = $newDesc;
      $ea->save();
    }
  }

  return [$em, $ef, $ea];
}

// Distribute the lexemes from the original entry among the new entries.
// Create new lexemes where necessary.
function assignLexemes($em, $ef, $ea, $lexemes) {

  // first distribute the M, F and A lexemes
  $mSatisfied = $fSatisfied = $aSatisfied = false;
  $hasMFLexeme = false;
  foreach ($lexemes as $l) {
    switch ($l->modelType) {
      case 'M':
        assert($em);
        printf("  * asociez lexemul existent [$l] cu intrarea de s.m.\n");
        EntryLexem::associate($em->id, $l->id);
        $mSatisfied = true;
        if (!hasTag($l, 'M')) {
          printf("  * etichetez lexemul existent [$l] cu [substantiv masculin]\n");
        }
        updateTags($l, ['M'], ['A', 'F', 'MF']);
        break;

      case 'F':
        assert($ef);
        printf("  * asociez lexemul existent [$l] cu intrarea de s.f.\n");
        EntryLexem::associate($ef->id, $l->id);
        $fSatisfied = true;
        if (!hasTag($l, 'F')) {
          printf("  * etichetez lexemul existent [$l] cu [substantiv feminin]\n");
        }
        updateTags($l, ['F'], ['A', 'M', 'MF']);
        break;

      case 'A':
        assert($ea);
        printf("  * asociez lexemul existent [$l] cu intrarea de adj.\n");
        EntryLexem::associate($ea->id, $l->id);
        $aSatisfied = true;
        if (!hasTag($l, 'A')) {
          printf("  * etichetez lexemul existent [$l] cu [adjectiv]\n");
        }
        updateTags($l, ['A'], ['F', 'M', 'MF']);
        break;

      case 'MF':
        $hasMFLexeme = true;
        // nothing else yet
        break;

      default:
        printf("  * nu recunosc tipul de model pentru {$l} {$l->modelType}{$l->modelNumber}; " .
               "îl asociez cu toate intrările\n");
        if ($em) {
          EntryLexem::associate($em->id, $l->id);
        }
        if ($ef) {
          EntryLexem::associate($ef->id, $l->id);
        }
        if ($ea) {
          EntryLexem::associate($ea->id, $l->id);
        }
    }
  }

  // Now split the MF lexemes, but only if the M and F entries don't already have lexemes.
  // If there are no MF lexemes, use the A lexemes instead.
  $splitModelType = $hasMFLexeme ? 'MF' : 'A';
  foreach ($lexemes as $l) {
    if ($l->modelType == $splitModelType) {
      splitLexeme($l, $em, $ef, $ea, $mSatisfied, $fSatisfied, $aSatisfied);
    }
  }
}

// figure out the feminine form for the new entry's description
function getFeminineForm($e) {
  $desc = $e->getShortDescription();

  // find an A or MF lexeme that generates this description
  $lexeme = null;
  foreach ($e->getLexems() as $l) {
    if (($l->formNoAccent == $desc) &&
        in_array($l->modelType, ['A', 'MF'])) {
      $lexeme = $l;
    }
  }
  if ($lexeme) {
    $femForm = InflectedForm::get_by_lexemId_inflectionId_variant(
      $lexeme->id, FEMININE_INFLECTION_ID, 0);
    return $femForm->formNoAccent;
  } else {
    return null;
  }
}

// splits an MF or A lexeme into M and F lexemes and associates them with the M and F entries
function splitLexeme($l, $em, $ef, $ea, $mSatisfied, $fSatisfied, $aSatisfied) {

  if (!array_key_exists($l->modelNumber, TYPE_MAP)) {
    printf("  * EROARE: nu știu cum să sparg lexemul {$l} {$l->modelType}{$l->modelNumber}\n");
    return;
  }

  if ($em && !$mSatisfied) {
    $mascNumber = TYPE_MAP[$l->modelNumber][0];
    printf("  * creez lexemul [{$l->form}] M{$mascNumber} asociat cu intrarea s.m. " .
           "și etichetat cu [s.m.]\n");
    $masc = cloneLexeme($l, $l->form, 'M', $mascNumber);
    EntryLexem::associate($em->id, $masc->id);
    updateTags($masc, ['M'], ['A', 'F', 'MF']);
  }

  if ($ef && !$fSatisfied) {
    foreach (TYPE_MAP[$l->modelNumber][1] as $variant => $femNumber) {
      $femForm = InflectedForm::get_by_lexemId_inflectionId_variant(
        $l->id, FEMININE_INFLECTION_ID, $variant);
      printf("  * creez lexemul [{$femForm->form}] F{$femNumber} asociat cu intrarea s.f. " .
             "și etichetat cu [s.f.]\n");
      $fem = cloneLexeme($l, $femForm->form, 'F', $femNumber);
      EntryLexem::associate($ef->id, $fem->id);
      updateTags($fem, ['F'], ['A', 'M', 'MF']);
    }
  }

  if ($ea && !$aSatisfied) {
    printf("  * schimb tipul lexemului {$l} {$l->modelType}{$l->modelNumber} în A, " .
           "îl asociez cu intrarea adj. și îl etichetez cu [adjectiv]\n");
    $l->modelType = 'A';
    $l->save(); // no need to regenerate the paradigm
    EntryLexem::associate($ea->id, $l->id);
    updateTags($l, ['A'], ['F', 'M', 'MF']);
  } else if ($l->modelType == 'MF') {
    // keep adjective lexemes
    $l->delete();
  }
}

// returns true iff the lexeme has a tag specific to the model type, e.g. [adjective] for 'A'
function hasTag($l, $modelType) {
  global $tags;

  $ot = ObjectTag::get_by_objectId_objectType_tagId(
    $l->id,
    ObjectTag::TYPE_LEXEM,
    $tags[$modelType]->id
  );
  return $ot;
}

function updateTags($l, $associate, $dissociate) {
  global $tags;

  foreach ($associate as $modelType) {
    ObjectTag::associate(ObjectTag::TYPE_LEXEM, $l->id, $tags[$modelType]->id);
  }
  foreach ($dissociate as $modelType) {
    ObjectTag::dissociate(ObjectTag::TYPE_LEXEM, $l->id, $tags[$modelType]->id);
  }
}

// different use case than Lexem::_clone()
function cloneLexeme($l, $form, $modelType, $modelNumber) {
  $c = $l->parisClone();
  $c->setForm($form);
  $c->modelType = $modelType;
  $c->modelNumber = $modelNumber;
  $c->restriction = '';
  $c->save();

  // copy sources and tags
  LexemSource::copy($l->id, $c->id, 1);
  foreach ($l->getObjectTags() as $ot) {
    ObjectTag::associate(ObjectTag::TYPE_LEXEM, $c->id, $ot->tagId);
  }

  // only now can we regenerate the paradigm, because certain tags dictate paradigm forms (e.g.
  // [admite vocativul] allows vocative forms.
  $c->regenerateParadigm();

  return $c;
}
