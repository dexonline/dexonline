<?php

$sourceMap = array(
  'der' => Source::get_by_urlName('der'),
  'dex' => Source::get_by_urlName('dex'),
  'dlrm' => Source::get_by_urlName('dlrm'),
  'dmlr' => Source::get_by_urlName('dmlr'),
  'doom' => Source::get_by_urlName('doom'),
  'nodex' => Source::get_by_urlName('nodex'),
  'orto' => Source::get_by_urlName('do'), // it differs here
);

$lexems = Model::factory('Lexem')->where_not_equal('source', '')->find_many();
$inserted = 0;
foreach ($lexems as $l) {
  $urlNames = explode(',', $l->source);
  foreach ($urlNames as $urlName) {
    $source = $sourceMap[$urlName];
    assert($source);
    $ls = Model::factory('LexemSource')->create();
    $ls->lexemId = $l->id;
    $ls->sourceId = $source->id;
    $ls->save();
    $inserted++;
  }
}

printf("%d lexems modified, %d lexem sources inserted.\n", count($lexems), $inserted);

?>
