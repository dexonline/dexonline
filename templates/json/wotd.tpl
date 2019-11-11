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
    "id": {$row->definition->id|@json_encode},
    "internalRep": {$row->definition->internalRep|escape:html|@json_encode},
    "htmlRep": {HtmlConverter::convert($row->definition)|@json_encode},
    "userNick": {$row->user->nick|@json_encode},
    "sourceName": {$row->source->shortName|@json_encode},
    "createDate": {$row->definition->createDate|@json_encode},
    "modDate": {$row->definition->modDate|@json_encode}
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
