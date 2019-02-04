<?php

/**
 * Detect spaced text that's written as f o o b a r instead of %foobar%.
 **/

require_once __DIR__ . '/../lib/Core.php';

const START_ID = 0;
const BATCH_SIZE = 10000;
const EXCLUDE_SOURCES = [32];

// Onomastic: @Actimie@ v. E f t i m i e @III 2.@
const PATTERN1 = '/^@[^@]+@ v\.(( \p{L})+)( @[^@]+@)?$/u';

// @Deodor@ v. D i o d o r.
const PATTERN2 = '/^@[^@]+@ v\.(( \p{L})+)\\.?$/u';

// Acțiunea de a (se) z g î r c i; -- with or without the parentheses
const PATTERN3 = '/Acțiunea de ((a \(?se\)?)(( \p{L})+))[ .;]/u';

// Acțiunea de a ș l e f u i;
const PATTERN4 = '/Acțiunea de ((a)(( \p{L})+))[ .;^]/u';

// Acțiunea de a (s e) p r o p a g a.
const PATTERN5 = '/Acțiunea de ((a \(s e\))(( \p{L})+))[ .;^]/u';

// @Dae@ v. R a d u @III 2@ și V l a d @III 2.@
const PATTERN6 = '/^@[^@]+@ v\.(( \p{L})+)( @[^@]+@)? și(( \p{L})+)( @[^@]+@)?$/u';

// same as 3-5, but with 'Faptul' instead of 'Acțiunea'
const PATTERN7 = '/Faptul de ((a \(?se\)?)(( \p{L}){3,}))[ .,;]/u';
const PATTERN8 = '/Faptul de ((a)(( \p{L}){3,}))[ .,;^]/u';
const PATTERN9 = '/Faptul de ((a \(s e\))(( \p{L}){3,}))[ .,;^]/u';

// Diminutiv al lui ș a t r ă.
const PATTERN10 = '/Diminutiv al lui(( \p{L}){3,})[ .,;^]/u';

// #v.# l o b o d ă.
const PATTERN11 = '/#[vV]\.#(( \p{L},?){3,})[ .,;)^]/u';

const PATTERN12 = '/\b(\p{L}\s+){2,}\p{L}\b/u';

$offset = 0;
$found = 0;

do {
  $defs = Model::factory('Definition')
        ->where_in('status', [0, 3])
        ->where_gte('id', START_ID)
        ->where_not_in('sourceId', EXCLUDE_SOURCES)
        ->order_by_asc('id')
        ->limit(BATCH_SIZE)
        ->offset($offset)
        ->find_many();

  foreach ($defs as $d) {
    $matches = [];
    $s = $d->internalRep;
    $case = 0;

    if (preg_match(PATTERN1, $d->internalRep, $matches, PREG_OFFSET_CAPTURE)) {
      $at = $matches[1][1];
      $len = strlen($matches[1][0]);
      $text = sprintf(' %%%s%%', str_replace(' ', '', $matches[1][0]));
      $d->internalRep = substr($s, 0, $at) . $text . substr($s, $at + $len);

      $case = 1;

    } else if (preg_match(PATTERN2, $d->internalRep, $matches, PREG_OFFSET_CAPTURE)) {
      $at = $matches[1][1];
      $len = strlen($matches[1][0]);
      $text = sprintf(' %%%s%%', str_replace(' ', '', $matches[1][0]));
      $d->internalRep = substr($s, 0, $at) . $text . substr($s, $at + $len);

      $case = 2;

    } else if (preg_match(PATTERN3, $d->internalRep, $matches, PREG_OFFSET_CAPTURE)) {
      $at = $matches[1][1];
      $len = strlen($matches[1][0]);
      $text = sprintf(
        '%%%s %s%%',
        $matches[2][0],
        str_replace(' ', '', $matches[3][0])
      );
      $d->internalRep = substr($s, 0, $at) . $text . substr($s, $at + $len);

      $case = 3;

    } else if (preg_match(PATTERN4, $d->internalRep, $matches, PREG_OFFSET_CAPTURE)) {
      $at = $matches[1][1];
      $len = strlen($matches[1][0]);
      $text = sprintf(
        '%%%s %s%%',
        $matches[2][0],
        str_replace(' ', '', $matches[3][0])
      );
      $d->internalRep = substr($s, 0, $at) . $text . substr($s, $at + $len);

      $case = 4;

    } else if (preg_match(PATTERN5, $d->internalRep, $matches, PREG_OFFSET_CAPTURE)) {
      $at = $matches[1][1];
      $len = strlen($matches[1][0]);
      $text = sprintf(
        '%%a (se) %s%%',
        str_replace(' ', '', $matches[3][0])
      );
      $d->internalRep = substr($s, 0, $at) . $text . substr($s, $at + $len);

      $case = 5;

    } else if (preg_match(PATTERN6, $d->internalRep, $matches, PREG_OFFSET_CAPTURE)) {
      $at1 = $matches[1][1];
      $len1 = strlen($matches[1][0]);
      $text1 = sprintf(' %%%s%%', str_replace(' ', '', $matches[1][0]));

      $at2 = $matches[4][1];
      $len2 = strlen($matches[4][0]);
      $text2 = sprintf(' %%%s%%', str_replace(' ', '', $matches[4][0]));

      $d->internalRep =
        substr($s, 0, $at1) .
        $text1 .
        substr($s, $at1 + $len1, $at2 - $at1 - $len1) .
        $text2 .
        substr($s, $at2 + $len2);

      $case = 6;

    } else if (preg_match(PATTERN7, $d->internalRep, $matches, PREG_OFFSET_CAPTURE)) {
      $at = $matches[1][1];
      $len = strlen($matches[1][0]);
      $text = sprintf(
        '%%%s %s%%',
        $matches[2][0],
        str_replace(' ', '', $matches[3][0])
      );
      $d->internalRep = substr($s, 0, $at) . $text . substr($s, $at + $len);

      $case = 7;

    } else if (preg_match(PATTERN8, $d->internalRep, $matches, PREG_OFFSET_CAPTURE)) {
      $at = $matches[1][1];
      $len = strlen($matches[1][0]);
      $text = sprintf(
        '%%%s %s%%',
        $matches[2][0],
        str_replace(' ', '', $matches[3][0])
      );
      $d->internalRep = substr($s, 0, $at) . $text . substr($s, $at + $len);

      $case = 8;

    } else if (preg_match(PATTERN9, $d->internalRep, $matches, PREG_OFFSET_CAPTURE)) {
      $at = $matches[1][1];
      $len = strlen($matches[1][0]);
      $text = sprintf(
        '%%a (se) %s%%',
        str_replace(' ', '', $matches[3][0])
      );
      $d->internalRep = substr($s, 0, $at) . $text . substr($s, $at + $len);

      $case = 9;

    } else if (preg_match(PATTERN10, $d->internalRep, $matches, PREG_OFFSET_CAPTURE)) {
      $at = $matches[1][1];
      $len = strlen($matches[1][0]);
      $text = sprintf(' %%%s%%', str_replace(' ', '', $matches[1][0]));
      $d->internalRep = substr($s, 0, $at) . $text . substr($s, $at + $len);

      $case = 10;

    } else if (preg_match(PATTERN11, $d->internalRep, $matches, PREG_OFFSET_CAPTURE)) {
      $at = $matches[1][1];
      $len = strlen($matches[1][0]);
      $tight = str_replace(' ', '', $matches[1][0]);
      $tight = str_replace(',', ', ', $tight);
      $text = sprintf(' %%%s%%', $tight);
      $d->internalRep = substr($s, 0, $at) . $text . substr($s, $at + $len);

      $case = 11;

    } else if (preg_match_all(
      PATTERN12,
      $d->internalRep,
      $matches,
      PREG_OFFSET_CAPTURE | PREG_SET_ORDER
    )) {
      // command line input
      $numChanges = 0;
      printf("%s %s\n", defUrl($d), $d->internalRep);
      foreach (array_reverse($matches) as $m) {
        $at = $m[0][1];
        $len = strlen($m[0][0]);
        $text = sprintf('%%%s%%', str_replace(' ', '', $m[0][0]));
        $s = substr($d->internalRep, 0, $at) . $text . substr($d->internalRep, $at + $len);
        
        $start = max($at - 20, 0);
        $end1 = $at + $len + 20;
        $end2 = $at + strlen($text) + 20;
        printf("%s\n", substr($d->internalRep, $start, $end1 - $start));
        printf("%s\n", substr($s, $start, $end2 - $start));
        
        $confirm = choice('Replace [y/n]?', ['y', 'n']);
        if ($confirm == 'y') {
          $d->internalRep = $s;
          $numChanges++;
        }
      }
      if ($numChanges) {
        $d->htmlRep = Str::htmlize($d->internalRep, $d->sourceId);
        $d->save();
      }
      $found++;
    }

    if ($case) {
      printf("%s case %d [%s] -> [%s]\n", defUrl($d), $case, $s, $d->internalRep);
      $d->htmlRep = Str::htmlize($d->internalRep, $d->sourceId);
      $d->save();
      $found++;
    }
  }

  $offset += count($defs);
  Log::info("$offset definitions reprocessed, $found matches.");
} while (count($defs));

Log::info("$offset definitions reprocessed, $found matches.");

/*************************************************************************/

function defUrl($d) {
  return "https://dexonline.ro/admin/definitionEdit.php?definitionId={$d->id}";
}

function choice($prompt, $choices) {
  do {
    $choice = readline($prompt . ' ');
  } while (!in_array($choice, $choices));
  return $choice;
}
