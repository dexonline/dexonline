<?php

require_once("../../phplib/Core.php");

User::mustHave(User::PRIV_EDIT);
Util::assertNotMirror();

$defId = Request::get('defId');
$similarId = Request::get('similarId');
$dstart = Request::get('dstart');
$dlen = Request::get('dlen');
$sstart = Request::get('sstart');
$slen = Request::get('slen');
$ins = Request::get('ins');

$def = Definition::get_by_id($defId);
$similar = Definition::get_by_id($similarId);

if ($ins) {
  $from = $def;
  $to = $similar;
  $fstart = $dstart;
  $flen = $dlen;
  $tstart = $sstart;
  $tlen = $slen;
} else {
  $from = $similar;
  $to = $def;
  $fstart = $sstart;
  $flen = $slen;
  $tstart = $dstart;
  $tlen = $dlen;
}

// copy text from $from to $to
$mid = substr($from->internalRep, $fstart, $flen);
if (!$tlen) {
  if ($tstart >= strlen($to->internalRep)) {
    $mid = " {$mid}";
  } else {
    $mid = "{$mid} ";
  }
}

$to->internalRep =
  substr($to->internalRep, 0, $tstart) .
  $mid .
  substr($to->internalRep, $tstart + $tlen);
$to->internalRep = Str::sanitize($to->internalRep, $to->sourceId);
$to->htmlRep = Str::htmlize($to->internalRep, $to->sourceId);

// TODO: make this page work (it no longer works after switching to FineDiff)
// $to->save();

Util::redirect("definitionEdit.php?definitionId={$defId}");
