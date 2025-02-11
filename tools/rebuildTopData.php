<?php

/**
 * Recomputes the contributions of every user.
 **/

require_once __DIR__ . '/../lib/Core.php';

// criteria for considering a definition as having been submitted in bulk
const BULK = [
  // nick          source (short)     createDate              1:ratio (3-> 33%, 4 -> 25%)
  [null,           'MDN \'00',        " = '2007-09-15'",      null],
  [null,           'Petro-Sedim',     null,                   null],
  [null,           'GTA',             null,                   null],
  [null,           'DCR2',            null,                   null],
  [null,           'DOR',             null,                   null],
#  ['raduborza',    'DOOM 2',          " > '2013-01-01'",         4],
  [null,           'DRAM',            null,                   null],
  [null,           'DRAM 2015',       null,                   null],
  ['siveco',       null,              null,                   null],
  ['RACAI',        null,              null,                   null],
];

function getSqlStatement(bool $hidden,  bool $manual, bool $lastYear) {
  $conditions = [];
  foreach (BULK as $tuple) {
    $parts = [];
    if ($tuple[0]) {
      $user = User::get_by_nick($tuple[0]);
      $parts[] = "(userId = {$user->id})";
    }
    if ($tuple[1]) {
      $src = Source::get_by_shortName($tuple[1]);
      $parts[] = "(sourceId = {$src->id})";
    }
    if ($tuple[2]) {
      $parts[] = "(left(from_unixtime(createDate), 10)" . $tuple[2] . ")";
    }
    if ($tuple[3]) {
      $parts[] = "(Definition.id % {$tuple[3]} != 0)";
    }
    $conditions[] = '(' . implode(' and ', $parts) . ')';
  }
  $clause = '(' . implode(' or ', $conditions) . ')';
  if ($manual) {
    $clause = "not {$clause}";
  }

  $statusClause = $hidden
    ? sprintf('status in (%d, %d)', Definition::ST_ACTIVE, Definition::ST_HIDDEN)
    : sprintf('status = %d', Definition::ST_ACTIVE);

  $timeClause =  $lastYear
    ? 'createDate > unix_timestamp(date_sub(curdate(), interval 1 year))'
    : 'true';

  $query = "select userId,
    count(*) as numDefs,
    sum(length(internalRep)) as numChars,
    max(createDate) as lastTimestamp
    from Definition
    where $clause
    and $statusClause
    and $timeClause
    group by userId";

  return $query;
}

function stats(bool $hidden,  bool $manual, bool $lastYear) {
  $query = getSqlStatement($hidden, $manual, $lastYear);
  Log::info(str_replace('%', '%%', $query));
  $dbResult = DB::execute($query);

  foreach ($dbResult as $row) {
    $te = Model::factory('TopEntry')->create();

    $te->hidden = $hidden;
    $te->manual = $manual;
    $te->lastYear = $lastYear;

    $te->userId = $row['userId'];
    $te->numChars = $row['numChars'];
    $te->numDefs = $row['numDefs'];
    $te->lastTimestamp = $row['lastTimestamp'];

    $te->save();
  }
}

function main() {
  Log::notice('started');
  DB::execute('truncate table TopEntry');

  stats(true, true, true);    // hidden, manual,    last year
  stats(true, true, false);   // hidden, manual,    all time
  stats(true, false, false);  // hidden, automatic, all time
  stats(false, true, true);   // public, manual,    last year
  stats(false, true, false);  // public, manual,    all time
  stats(false, false, false); // public, automatic, all time

  Log::notice('finished');
}

main();
