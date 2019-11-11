{assign var="results" value=$results|default:null}
{
"type": "searchResults",
"word": {$cuv|@json_encode},
"definitions": [
{foreach $results as $row}
  {
  "type": "definition",
  {include "json/definition.tpl"}
  }{if !$row@last},{/if}
{/foreach}
]
}
