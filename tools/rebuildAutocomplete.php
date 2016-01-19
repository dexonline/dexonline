<?php

require_once __DIR__ . '/../phplib/util.php';

log_scriptLog('rebuildAutocomplete: starting');

db_execute("truncate table Autocomplete");
db_execute("insert into Autocomplete select distinct formNoAccent, formUtf8General from Lexem");

log_scriptLog('rebuildAutocomplete: ending');
