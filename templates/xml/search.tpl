{assign var="results" value=$results|default:null}
<?xml version="1.0" encoding="UTF-8" ?>
<searchResults>
  <word>{$cuv}</word>
  <definitions>
    {foreach $results as $row}
      {include "xml/definition.tpl"}
    {/foreach}
  </definitions>
</searchResults>
