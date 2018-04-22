<?php

require_once("../../phplib/Core.php");
User::mustHave(User::PRIV_EDIT);
Util::assertNotMirror();

$action = Request::get('action');
$abbrevId = Request::get('abbrevId');
$sourceId = Request::get('sourceId');
$short = Request::get('short');
$internalRep = Request::get('internalRep');
$enforced = Request::has('enforced');
$ambiguous = Request::has('ambiguous');
$caseSensitive = Request::has('caseSensitive');
$userId = User::getActiveId();
$html = '';


if (!$abbrevId) {
  $abbrev = Model::factory('Abbreviation')->create();
  $abbrev->sourceId = $sourceId;
} else {
  $abbrev = Model::factory('Abbreviation')
                   ->where('id', $abbrevId)
                   ->find_one();
}

/** Populate the fields with new values and save */
$abbrev->short = $short;
$abbrev->internalRep = $internalRep;
list($abbrev->htmlRep, $ignored) = Str::htmlize($internalRep, $sourceId);
$abbrev->enforced = $enforced;
$abbrev->ambiguous = $ambiguous;
$abbrev->caseSensitive = $caseSensitive;
$abbrev->modUserId = $userId;
$abbrev->save();

/** Prepare the false checkboxes from within the row */
$tableData = '';
$props = [$enforced, $ambiguous, $caseSensitive];
foreach ($props as $checked) {
  $tableData .= '<td>' .
                  '<label class="label label-' . ($checked ? 'success' : 'primary') . '">' .
                    '<i class="glyphicon glyphicon-' . ($checked ? 'ok' : 'minus') 
                      . '" data-checked="' . $checked . '"></i>' .
                  '</label>' .
                '</td>';
}

/** Render the entire row */
$html = '<tr id="' . $abbrev->id . '">' .
          '<td>' .
            '<span class="label label-primary">' . $abbrev->id . '</span>' .
          '</td>' .
          $tableData .
          '<td>' . $short . '</td>' .
          '<td>' . $internalRep . '</td>' .
          '<td>' .
            '<div class="btn-group btn-group">' .
              '<button type="button" class="btn btn-xs btn-default" name="btn-edit" data-row-id="'. 
                 $abbrev->id . '"><i class="glyphicon glyphicon-edit"></i></button>' .
              '<button type="button" class="btn btn-xs btn-default" data-row-id="' . 
                 $abbrev->id . '"><i class="glyphicon glyphicon-trash"></i></button>' .
            '</div>' .
          '</td>' .
        '</tr>';

$response = [ 'id' => $abbrev->id,
              'action' => $action,
              'html' => $html, ];

header('Content-Type: application/json');
print json_encode($response);
