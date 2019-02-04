<?php
require_once '../phplib/Core.php';

if (count($_GET) == 0) {
  Util::redirect("https://wiki.dexonline.ro/wiki/Protocol_de_exportare_a_datelor_v5");
}

$x = new XmlDump(5);
$lastDump = $x->getLastDumpDate();

$lastClientUpdate = Request::get('last', '0');
if ($lastClientUpdate == '0') {
  if (!$lastDump) {
    SmartyWrap::assign('noFullDump', true);
  } else {
    // Dump the freshest full dump we have
    SmartyWrap::assign('serveFullDump', true);
    $lastClientUpdate = $lastDump;
  }
}

SmartyWrap::assign([
  'lastDump' => $lastDump,
  'url' => $x->getUrl(),
  'diffs' => $x->getDiffsSince($lastClientUpdate),
]);

header('Content-type: text/xml');
print SmartyWrap::fetch('xml/update5.tpl');
