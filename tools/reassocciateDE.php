<?php

require_once __DIR__ . '/../phplib/util.php';

$SERVER_URL = 'https://dexonline.ro';
$SOURCE_ID = 25; // Dicționarul enciclopedic

// Parse the optional --start argument
$opts = getopt('', ['start:']);
$start = isset($opts['start']) ? $opts['start'] : '';

// Load definitions from DE
$result = Model::factory('Definition')
        ->where('sourceId', $SOURCE_ID)
        ->where('status', Definition::ST_ACTIVE)
        ->where_gte('lexicon', $start)
        ->order_by_asc('lexicon')
        ->limit(100)
        ->find_result_set();
$count = count($result);

foreach($result as $i => $d) {
  // Load the lexem set
  $lexems = Model::factory('Lexem')
          ->select('Lexem.*')
          ->join('LexemDefinitionMap', ['Lexem.id', '=', 'lexemId'])
          ->where('LexemDefinitionMap.definitionId', $d->id)
          ->find_many();

  // Print the definition and lexems
  print("\n\n**** [{$d->lexicon}]\t\t[{$SERVER_URL}/admin/definitionEdit.php?definitionId={$d->id}]\t\t({$i}/{$count})\n");
  printf(mb_substr($d->internalRep, 0, 120) . "\n");
  foreach ($lexems as $l) {
    $lms = implode('/', $l->getLexemModels());
    print("  * Lexem: {$l->form} ($lms) ({$l->id})\n");
  }

  $done = false;
  while (!$done) {
    $errors = [];
    $line = myReadline('Lexeme de asociat: ');
    $line = trim($line);
    if (empty($line)) {
      // Empty line means ignore this definition
      $line = myReadline('Ignor această definiție? [D/n] ');
      $done = ($line != 'n');
    } else {
      // Parse the proposed lexem list
      $new = [];
      foreach (split(';', $line) as $chunk) {
        // Parse the lexem form (or ID) and model
        $parts = split('/', $chunk);
        $form = trim($parts[0]);
        if (count($parts) == 2) {
          list($modelType, $modelNumber) = parseModel(trim($parts[1]));
          if (!FlexModel::loadCanonicalByTypeNumber($modelType, $modelNumber)) {
            $errors[] = "Modelul {$parts[1]} nu există.";
          }
        } else {
          $modelType = $modelNumber = null;
        }
        if (count($parts) > 2) {
          $errors[] = "Lexemul [{$chunk}] conține prea multe slashuri";
        }

        // Find or create a lexem with the given form/ID and model
        if (is_numeric($form)) {
          $l = Lexem::get_by_id($form);
          if ($l) {
            $new[] = $l;
          } else {
            $errors[] = "Nu există niciun lexem cu ID-ul $form";
          }
        } else {
          $matches = Model::factory('Lexem')
                   ->where_any_is([
                     ['form' => $form],
                     ['formNoAccent' => $form]
                   ])
                   ->find_many();
          if (count($matches) == 0) {
            if ($modelType) {
              $l = Lexem::deepCreate($form, $modelType, $modelNumber);
            } else {
              $l = Lexem::deepCreate($form, 'I', 3);
            }
            $l->noAccent = (strstr($form, "'") === false);
            $new[] = $l;
          } else if (count($matches) == 1) {
            if ($modelType) {
              $new[] = lexemWithModel($matches[0], $modelType, $modelNumber);
            } else {
              $new[] = $matches[0];
            }
          } else {
            $msg = "Lexemul [{$chunk}] este ambiguu:";
            foreach ($matches as $m) {
              $msg .= " {$m} ({$m->id});";
            }
            $errors[] = $msg;
          }
        }
      }

      // Print errors or confirm proposed actions
      if (count($errors)) {
        printf("Erori:\n");
        foreach($errors as $e) {
          printf("  * {$e}\n");
        }
      } else {
        printf("Lexeme propuse:\n");
        foreach ($new as $l) {
          $lms = implode('/', $l->getLexemModels());
          $action = '';
          if (!$l->id) {
            $action = 'CREEZ ';
          } else if (!$l->getFirstLexemModel()->id) {
            $action = 'SCHIMB MODELUL ';
          }
          print("  * {$action}{$l->form} ($lms) ({$l->id})\n");
        }
        $line = myReadline('De acord? [D/n] ');
        $done = ($line != 'n');
        if ($done) {

          // Perform the proposed actions

          // Delete the old associations
          $ldms = LexemDefinitionMap::deleteByDefinitionId($d->id);

          foreach ($new as $l) {
            // Update the lexem models if necessary
            if (!$l->id) {
              $l->deepSave();
            } else if (!$l->getFirstLexemModel()->id) {
              $original = Lexem::get_by_id($l->id);
              foreach ($original->getLexemModels() as $lm) {
                $lm->delete(); // This will also delete LexemSources and InflectedForms
              }
              $l->deepSave();
            }
            LexemDefinitionMap::associate($l->id, $d->id);
          }

          // If any of the original lexems are now unassociated with any definitions,
          // offer to delete them.
          foreach ($lexems as $l) {
            $l = Lexem::get_by_id($l->id); // force a refresh
            $ldms = LexemDefinitionMap::get_all_by_lexemId($l->id);
            if (!count($ldms) && !$l->isLoc()) {
              $delete = myReadline("Șterg lexemul neasociat {$l}? [D/n] ");
              if ($delete != 'n') {
                $l->delete();
              }
            }
          }
        }
      }
    }
  }
}

/***************************************************************************/

// Returns an array [$modelType, $modelNumber]. No support for restrictions.
function parseModel($s) {
  $len = mb_strlen($s);
  $i = 0;
  while (($i < $len) && ctype_upper(StringUtil::getCharAt($s, $i))) {
    $i++;
  }
  return [mb_substr($s, 0, $i), mb_substr($s, $i)];
}

// Creates a new lexem Model for the given lexem
function lexemWithModel($l, $modelType, $modelNumber) {
  $lm = Model::factory('LexemModel')->create();
  $lm->lexemId = $l->id;
  $lm->setLexem($l); // Otherwise it will reload the original
  $lm->displayOrder = 1;
  $lm->modelType = $modelType;
  $lm->modelNumber = $modelNumber;
  $lm->restriction = '';
  $lm->tags = '';
  $lm->isLoc = 0;
  $lm->generateInflectedFormMap();

  $lexemModels = [$lm];
  $l->setLexemModels($lexemModels);
  return $l;
}

// Readline with UTF-8 is broken on Ubuntu. Use rlwrap and naive fgets.
function myReadline($prompt) {
  print($prompt);
  return trim(fgets(STDIN));
}
