<?php

$definitions = Model::factory('Definition')
             ->select('id')
             ->select('internalRep')
             ->where_in('status', [Definition::ST_ACTIVE, Definition::ST_HIDDEN])
             ->where_like('internalRep', '%|%|%|%')
             ->find_many();

file_put_contents("def_in_word.txt", "Definiție este subșir al lui cuvânt\n\n", FILE_APPEND);
file_put_contents("levenshtein.txt", "Distanță suficient de mică\n\n", FILE_APPEND);
file_put_contents("same_lexem.txt", "Același lexem\n\n", FILE_APPEND);
file_put_contents("no_change.txt", "Nu s-a efectuat nicio modificare\n\n", FILE_APPEND);

foreach ($definitions as $d) {
    preg_match_all("/\|([^\|]+)\|([^\|]+)\|/", $d->internalRep, $links, PREG_SET_ORDER);
    foreach ($links as $l) {
        $words = trim($l[1], "$#@^_0123456789");
        $definition_string = trim($l[2], "$#@^_0123456789");

        $levDist = Levenshtein::dist($words, $definition_string);
        if ($levDist <= 50) {
            $fname = "levenshtein.txt";
        } else {
            foreach (explode(" ", $words) as $word_string) {
                $fname = "no_change.txt";
                if (strcasecmp(substr($word_string, 0, strlen($definition_string)), $definition_string) === 0) {
                    $fname = "def_in_word.txt";
                    break;
                } else {
                    $word_lexem_ids = Model::factory('InflectedForm')
                     ->select('lexemId')
                     ->where('formNoAccent', $word_string)
                     ->find_many();

                    $def_lexem_ids = Model::factory('InflectedForm')
                     ->select('lexemId')
                     ->where('formNoAccent', $definition_string)
                     ->find_many();

                    if (empty($def_lexem_ids)) {
                        $fname = "same_lexem.txt";
                        break;
                    }

                    $lexem_match = false;
                    foreach ($word_lexem_ids as $word_lexem_id) {
                        foreach ($def_lexem_ids as $def_lexem_id) {
                            if (abs($word_lexem_id->lexemId - $def_lexem_id->lexemId) <= 5) {
                                $lexem_match = true;
                            }
                        }
                    }

                    if ($lexem_match === true) {
                        $fname = "same_lexem.txt";
                        break;
                    }
                }
            }
        }

        file_put_contents($fname, $words . "," . $definition_string . " (id: " . $d->id . ") (lev: " . $levDist . ")\n", FILE_APPEND);
    }
}
