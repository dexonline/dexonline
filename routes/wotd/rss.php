<?php

const DELAY=0;

$words = WordOfTheDay::getRSSWotD(DELAY);
$results = [];
foreach ($words as $w) {

  $ts = strtotime($w->displayDate);
  $def = $w->getDefinition();
  $source = Source::get_by_id($def->sourceId);

  Smart::assign([
    'def' => $def,
    'source' => $source,
    'reason' => Str::htmlize($w->description)[0],
    'imageUrl' => $w->getLargeThumbUrl(),
    'html' => HtmlConverter::convert($def),
  ]);
  $description = Smart::fetch('bits/wotdRssItem.tpl');
  $pubDate = date('r', $ts);
  $url = Router::link('wotd/view', true) . '/' . date('Y/m/d', $ts);

  $results[] = [
    'title' => $def->lexicon,
    'description' => $description,
    'pubDate' => $pubDate,
    'link' => $url,
  ];
}

header("Content-type: application/rss+xml; charset=utf-8");
Smart::assign([
  'rss_title' => 'Cuvântul zilei',
  'rss_link' => Router::link('wotd/rss', true),
  'rss_description' => 'Doza zilnică de cuvinte de la dexonline!',
  'rss_pubDate' => date('D, d M Y H:i:s') . ' EEST',
  'results' => $results,
]);
Smart::displayWithoutSkin('xml/rss.tpl');
