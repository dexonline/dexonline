<?php

/**
 * Merge duplicate meanings from DEX '98 and DEX '09.
 **/

require_once __DIR__ . '/../phplib/Core.php';

const START_AT = 'puchițek';
const FEMININE_INFLECTION_ID = 33;

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
];

$tags = [
  'MF' => Tag::get_by_value('substantiv masculin și feminin'),
  'M' => Tag::get_by_value('substantiv masculin'),
  'F' => Tag::get_by_value('substantiv feminin'),
  'N' => Tag::get_by_value('substantiv neutru'),
  'A' => Tag::get_by_value('adjectiv'),
];

// load entries having lexemes of types 'MF' or 'A'
$entries = Model::factory('Entry')
         ->table_alias('e')
         ->select('e.*')
         ->distinct()
         ->join('EntryLexem', ['e.id', '=', 'el.entryId'], 'el')
         ->join('Lexem', ['el.lexemId', '=', 'l.id'], 'l')
         ->where_in('l.modelType', ['MF', 'A'])
         ->where_gt('e.description', START_AT)
         ->order_by_asc('e.description')
         ->find_many();

foreach ($entries as $em) {
  // Split the entry if it has any noun-type lexemes. This is OK to do, because either it
  // has an MF lexeme or it has an A lexeme (per the above query) and a noun lexeme.
  $toSplit = Model::factory('Lexem')
           ->table_alias('l') 
           ->join('EntryLexem', ['l.id', '=', 'el.lexemId'], 'el')
           ->where('el.entryId', $em->id)
           ->where_in('l.modelType', [ 'MF', 'M', 'F', ])
           ->count();

  if ($toSplit) {

    printf("==== procesez intrarea [$em]\n");

    $femForm = getFeminineForm($em);

    if (!$femForm) {
      printf("  * EROARE: nu pot deduce forma de feminin pentru [{$em}](ID intrare={$em->id})\n");
    } else {
      $shortDesc = $em->getShortDescription();
      $femDesc = str_replace($shortDesc, $femForm, $em->description);
      printf("  * creez intrarea pentru feminin: [{$femDesc}]\n");
      $ef = $em->_clone(true, false, true, true); // do not clone lexeme associations
      $ef->description = $femDesc;
      $ef->save();
      foreach ($em->getLexems() as $l) {
        switch ($l->modelType) {
          case 'M':
            // do nothing: lexeme already properly associated with the masculine entry
            printf("  * lexemul [$l] rămâne asociat cu intrarea la masculin\n");
            break;

          case 'F':
            // move lexeme to the feminine entry
            printf("  * lexemul [$l] se mută la intrarea la feminin\n");
            EntryLexem::dissociate($em->id, $l->id);
            EntryLexem::associate($em->id, $l->id);
            break;

          case 'A':
          case 'MF':
            // break into M and F lexemes
            splitLexeme($l, $em, $ef);

            // keep a copy of the MF/A lexeme if it is an adjective
            if (($l->modelType == 'A') || hasAdjectiveTag($l)) {
              printf("  * păstrez o copie a lui {$l} A{$l->modelNumber}, asociat " .
                     "cu ambele intrări\n");
              $l->modelType = 'A';
              $l->save(); // no need to regenerate the paradigm
              EntryLexem::associate($ef->id, $l->id);
            } else {
              $l->delete();
            }
            break;

          default:
            printf("  * nu recunosc tipul de model pentru {$l} {$l->modelType}{$l->modelNumber}; " .
                   "îl las asociat cu intrarea la masculin\n");
        }
      }
    }

  } else {
    // printf("skipping $em\n");
  }
  
}

/*************************************************************************/

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
function splitLexeme($l, $em, $ef) {
  if (!array_key_exists($l->modelNumber, TYPE_MAP)) {
    printf("  * EROARE: nu știu cum să sparg lexemul {$l} {$l->modelType}{$l->modelNumber}\n");
    return;
  }

  $mascNumber = TYPE_MAP[$l->modelNumber][0];
  printf("  * creez lexemul [{$l->form}] M{$mascNumber} asociat cu intrarea la masculin\n");
  $masc = cloneLexeme($l, $l->form, 'M', $mascNumber);
  EntryLexem::associate($em->id, $masc->id);

  foreach (TYPE_MAP[$l->modelNumber][1] as $variant => $femNumber) {
    $femForm = InflectedForm::get_by_lexemId_inflectionId_variant(
      $l->id, FEMININE_INFLECTION_ID, $variant);
    printf("  * creez lexemul [{$femForm->form}] F{$femNumber} asociat cu intrarea la feminin\n");
    $fem = cloneLexeme($l, $femForm->form, 'F', $femNumber);
    EntryLexem::associate($ef->id, $fem->id);
  }
}

// returns true iff the lexeme has an [adjective] tag
function hasAdjectiveTag($l) {
  global $tags;

  $ot = ObjectTag::get_by_objectId_objectType_tagId(
    $l->id,
    ObjectTag::TYPE_LEXEM,
    $tags['A']->value
  );
  return ($ot !== null);
}

// different use case than Lexem::_clone()
function cloneLexeme($l, $form, $modelType, $modelNumber) {
  $c = $l->parisClone();
  $c->setForm($form);
  $c->modelType = $modelType;
  $c->modelNumber = $modelNumber;
  $c->restriction = '';
  $c->save();
  $c->regenerateParadigm();

  // copy sources and tags
  LexemSource::copy($l->id, $c->id, 1);
  foreach ($l->getObjectTags() as $ot) {
    ObjectTag::associate(ObjectTag::TYPE_LEXEM, $c->id, $ot->tagId);
  }

  return $c;
}
