<?php

require_once __DIR__ . '/../phplib/util.php';

Log::notice('started');

db_execute("truncate table Autocomplete");
db_execute("insert into Autocomplete select distinct formNoAccent, formUtf8General from Lexem");

Log::notice('finished');
