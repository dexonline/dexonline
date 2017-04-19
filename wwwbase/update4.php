<?php
require_once("../phplib/Core.php");

if (count($_GET) == 0) {
  Util::redirect("http://wiki.dexonline.ro/wiki/Protocol_de_exportare_a_datelor");
}

$x = new XmlDump(4);
$lastDump = $x->getLastDumpDate();

$lastClientUpdate = Request::get('last', '0');
if ($lastClientUpdate == '0') {
  // Dump the freshest full dump we have
  // TODO: return an error if there is no full dump
  SmartyWrap::assign('serveFullDump', true);
  $lastClientUpdate = $lastDump;
}

SmartyWrap::assign('lastDump', $lastDump);
SmartyWrap::assign('url', $x->getUrl());
SmartyWrap::assign('diffs', $x->getDiffsSince($lastClientUpdate));

header('Content-type: text/xml');
print SmartyWrap::fetch('xml/update4.tpl');
