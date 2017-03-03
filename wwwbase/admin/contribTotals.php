<?php

require_once("../../phplib/util.php"); 
util_assertModerator(PRIV_ADMIN);
util_assertNotMirror();

$submitButton = Request::has('submitButton');

if ($submitButton) {
  $userId = Request::get('userId');
  $startDate = Request::get('startDate');
  $endDate = Request::get('endDate');

  $errors = validate($userId, $startDate, $endDate);
  if ($errors) {
    SmartyWrap::assign('errors', $errors);
  } else {

    // Compute the definition totals for the given parameters
    $results = Model::factory('Definition')
             ->table_alias('d')
             ->select_expr('sum(length(internalRep))', 'length')
             ->select('s.id')
             ->select('s.shortName')
             ->join('Source', ['d.sourceId', '=', 's.id'], 's')
             ->where('d.userId', $userId)
             ->where_in('d.status', [Definition::ST_ACTIVE, Definition::ST_HIDDEN])
             ->where_raw("(date(from_unixtime(d.createDate)) between ? and ?)",
                         [$startDate, $endDate])
             ->group_by('s.id')
             ->order_by_desc('length')
             ->find_many();


    $sumLength = 0;
    foreach ($results as $row) {
      $sumLength += $row->length;
    }

    // Load each definition and compare it with the OCR version (if available)
    $defs = Model::factory('Definition')
          ->table_alias('d')
          ->left_outer_join('OCR', ['d.id', '=', 'o.definitionId'], 'o')
          ->where('d.userId', $userId)
          ->where_in('d.status', [Definition::ST_ACTIVE, Definition::ST_HIDDEN])
          ->where_raw("(date(from_unixtime(d.createDate)) between ? and ?)",
                      [$startDate, $endDate])
          ->find_many();

    $changes = [];
    $sumChanges = 0;
    foreach ($results as $row) {
      $changes[$row->id] = 0;
    }

    foreach ($defs as $d) {
      if ($d->definitionId) { // there exists a corresponding OCR record
        $diffSize = count(LDiff::textDiff($d->internalRep, $d->ocrText));
        $changes[$d->sourceId] += $diffSize;
        $sumChanges += $diffSize;
      }
    }

    SmartyWrap::assign('results', $results);
    SmartyWrap::assign('changes', $changes);
    SmartyWrap::assign('sumLength', $sumLength);
    SmartyWrap::assign('sumChanges', $sumChanges);
  }
} else {
  $userId = null;
  list($startDate, $endDate) = getPreviousTrimester();
}

SmartyWrap::assign('userId', $userId);
SmartyWrap::assign('startDate', $startDate);
SmartyWrap::assign('endDate', $endDate);
SmartyWrap::addCss('admin');
SmartyWrap::addJs('select2Dev');
SmartyWrap::display('admin/contribTotals.tpl');

/*************************************************************************/

function validate($userId, $startDate, $endDate) {
  $errors = [];

  if (!$userId) {
    $errors['userId'][] = 'Trebuie să alegeți un utilizator.';
  }

  if (!$startDate) {
    $errors['startDate'][] = 'Data de început nu poate fi vidă.';
  }

  if (!$endDate) {
    $errors['endDate'][] = 'Data de sfârșit nu poate fi vidă.';
  }

  if ($startDate && $endDate && ($startDate > $endDate)) {
    $errors['endDate'][] = 'Datele trebuie să fie în ordine cronologică.';
  }

  return $errors;
}

// Gets the first and last days of the previous trimester
function getPreviousTrimester() {
  // number of days in the last month of the trimester
  $numDaysInMonth = [ 31 /* March */, 30 /* June */, 30 /* September */, 31 /* December */ ];

  $year = date('Y');
  $month = date('n') - 1; // convert to 0-based

  $trim = $month / 3; // 0-based
  $prevTrim = ($trim + 3) % 4;
  if ($prevTrim == 3) {
    $year--;
  }

  $startMonth = $prevTrim * 3 + 1; // back to 1-based
  $endMonth = $startMonth + 2;
  $daysEndMonth = $numDaysInMonth[$prevTrim];

  $startDate = sprintf('%d-%02d-01', $year, $startMonth);
  $endDate = sprintf('%d-%02d-%d', $year, $endMonth, $daysEndMonth);

  return [$startDate, $endDate];
}
