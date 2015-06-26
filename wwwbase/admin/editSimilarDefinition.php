<?php

require_once("../../phplib/util.php"); 
util_assertModerator(PRIV_EDIT);
util_assertNotMirror();

$defId = util_getRequestParameter('defId');
$similarId = util_getRequestParameter('similarId');
$dstart = util_getRequestParameter('dstart');
$dlen = util_getRequestParameter('dlen');
$sstart = util_getRequestParameter('sstart');
$slen = util_getRequestParameter('slen');
$ins = util_getRequestParameter('ins');

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
$to->internalRep = AdminStringUtil::internalizeDefinition($to->internalRep, $to->sourceId);
$to->htmlRep = AdminStringUtil::htmlize($to->internalRep, $to->sourceId);
$to->save();

util_redirect("definitionEdit.php?definitionId={$defId}");

?>
