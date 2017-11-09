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

            // Interogari separate pentru formNoAccent și formUtf8General
            // pentru ca Idiorm nu suporta OR in clauze WHERE.
            //
            $def_lexem_id_by_noAccent = Model::factory('Lexem')
                ->select('id')
                ->where('formNoAccent', $definition_string)
                ->find_one();

            $def_lexem_id_by_utf8General = Model::factory('Lexem')
                ->select('id')
                ->where('formUtf8General', $definition_string)
                ->find_one();

            // Nu exista lexem cu forma din definitie.
            //
            if (empty($def_lexem_id_by_utf8General)) {
                $verdict = "no_link";
                break;
            }

            // Trimitere catre forma de baza.
            //
            $found = false;
            foreach ($word_lexem_ids as $word_lexem_id) {
                if ($word_lexem_id->lexemId === $def_lexem_id_by_noAccent->id) {
                    $found = true;
                }
            }

            if ($found === true) {
                $verdict = "forma_baza";
                break;
            }

            // Infinitiv lung / adjectiv / participiu.
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
                        if ($one->lexemId === $def_lexem_id_by_noAccent->id) {
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

        print $l[1] . "," . $l[2] . "," . $d->id . "," . $verdict . ",";
        print "[definiție]" . "(https://dexonline.ro/definitie/" . $d->id . "),";
        print "[editează]" . "(https://dexonline.ro/admin/definitionEdit.php?definitionId=" . $d->id . ")\n";
    }
}

?>
