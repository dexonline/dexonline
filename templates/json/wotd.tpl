{assign var="row" value=$searchResult|default:null}
{
"type": "results",
"day": {$day|@json_encode},
"month": {$month|@json_encode},
"requested": {
  "record": {
  "year": {$year|@json_encode},
  "word": {$row->definition->lexicon|@json_encode},
  "reason": {$reason|@json_encode},
  "image": {$wotd->getLargeThumbUrl()|@json_encode},
  "definition": {
    {include "json/definition.tpl"}
  }
  }
} ,
"others": { 
  "record": [
    {foreach $otherYears as $row}
    {
    "year": {$row.wotd->displayDate|date_format:'%Y'|@json_encode},
    "word": {$row.word|@json_encode},
    "reason": {$row.wotd->description|@json_encode},
    "image": {$row.wotd->getMediumThumbUrl()|@json_encode}
    }
    {if !$row@last},{/if}
    {/foreach}
  ]
}
}
