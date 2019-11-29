<?xml version="1.0" encoding="UTF-8" ?>
<results>
  <day>{$day}</day>
  <month>{$month}</month>
  <requested>
    <record>
      <year>{$year}</year>
      <word>{$searchResult->definition->lexicon}</word>
      <reason>{$reason|escape:html}</reason>
      <image>{$wotd->getLargeThumbUrl()}</image>
      {include "xml/bits/definition.tpl"}
    </record>
  </requested>
  <others>
    {foreach $otherYears as $row}
    <record>
      <year>{$row.wotd->displayDate|date_format:'%Y'}</year>
      <word>{$row.word}</word>
      <reason>{$row.wotd->description|escape:html}</reason>
      <image>{$row.wotd->getMediumThumbUrl()}</image>
    </record>
    {/foreach}
  </others>
</results>
