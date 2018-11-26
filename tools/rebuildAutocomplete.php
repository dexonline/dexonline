<?php

require_once __DIR__ . '/../phplib/Core.php';

Log::notice('started');

DB::execute('truncate table Autocomplete');
DB::execute('insert into Autocomplete ' .
            'select distinct formNoAccent, formUtf8General ' .
            'from Lexeme ' .
            'where not apheresis');

Log::notice('finished');
