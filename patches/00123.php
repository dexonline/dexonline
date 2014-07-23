<?php

$FROM = array(
  '—',
  '◊',
  '♦',
  '[#Pr.#: -$',
  '$-]',
  '„',
  '”',
  '@ @',
);

$TO = array(
  '-',
  '*',
  '**',
  '[#Pr.#: $-',
  '-$]',
  '"',
  '"',
  ' ',
);

$FROM_REGEXP = array(
  '/@\(([0-9])\)([.,]?)@/',
  '/\^\{([0-9])\}/',
);

$TO_REGEXP = array(
  '(@$1@)$2',
  '^$1',
);

$dex09 = Source::get_by_urlName('dex09');
$defs = Model::factory('Definition')
  ->where('sourceId', $dex09->id)
  ->find_result_set();
$num = count($defs);

foreach ($defs as $i=> $def) {
  $def->internalRep = str_replace($FROM, $TO, $def->internalRep);
  $def->internalRep = preg_replace($FROM_REGEXP, $TO_REGEXP, $def->internalRep);

  $def->htmlRep = AdminStringUtil::htmlize($def->internalRep, $def->sourceId);
  $def->save();
  if ($i % 1000== 0) {
    printf("%d of %d definitions processed\n", $i, $num);
  }
}

?>
