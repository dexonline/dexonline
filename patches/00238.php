<?php

$definitions = Model::factory('Definition')
->select('id')
->select('internalRep')
->where_in('status', [Definition::ST_ACTIVE, Definition::ST_HIDDEN])
->where_like('internalRep', '%|%|%|%')
->find_many();

foreach ($definitions as $d) {
    preg_match_all("/\|([^\|]+)\|([^\|]+)\|/", $d->internalRep, $links, PREG_SET_ORDER);
    foreach ($links as $l) {
        $words = trim($l[1], "$#@^_0123456789");
        $definition_string = trim($l[2], "$#@^_0123456789");

        $verdict = "nemodificat";

        foreach (explode(" ", $words) as $word_string) {
            $word_lexem_ids = Model::factory('InflectedForm')
                ->select('lexemId')
                ->where('formNoAccent', $word_string)
                ->find_many();

            $def_lexem_id = Model::factory('Lexem')
                ->select('id')
                ->where('formNoAccent', $definition_string)
                ->find_one();

            // Trimitere către forma de bază
            //
            if (empty($def_lexem_id)) {
                $verdict = "forma_baza";
                break;
            }

            $found = false;
            foreach ($word_lexem_ids as $word_lexem_id) {
                if ($word_lexem_id->lexemId === $def_lexem_id->id) {
                    $found = true;
                }
            }

            if ($found === true) {
                $verdict = "forma_baza";
                break;
            }

            // Infinitiv lung / adjectiv / participiu
            //
            $found = false;

            foreach ($word_lexem_ids as $word_lexem_id) {
                $lexem_model = Model::factory('Lexem')
                    ->select('formNoAccent')
                    ->select('modelType')
                    ->select('modelNumber')
                    ->where_id_is($word_lexem_id->lexemId)
                    ->find_one();

                if ($lexem_model->modelType === "IL" ||
                    $lexem_model->modelType === "PT" ||
                    $lexem_model->modelType === "A" ||
                    ($lexem_model->modelType === "F" &&
                    ($lexem_model->modelNumber === "107" ||
                    $lexem_model->modelNumber === "113"))) {
                    $nextstep = Model::factory('InflectedForm')
                        ->select('lexemId')
                        ->where('formNoAccent', $lexem_model->formNoAccent)
                        ->find_many();

                    foreach ($nextstep as $one) {
                        if ($one->lexemId === $def_lexem_id->id) {
                            $found = true;
                            break;
                        }
                    }
                }
            }

            if ($found === true) {
                $verdict = "inf_lung";
                break;
            }
        }

        print $words . "," . $definition_string . "," . $d->id . "," . $verdict . "\n";
    }
}

?>
