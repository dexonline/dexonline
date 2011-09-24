<?xml version="1.0" encoding="UTF-8"?>
<AbbrevList>
  <Sources>
    {foreach from=$sources key=sourceId item=source}
      <Source id="{$sourceId}">
        {foreach from=$source item=section}
          <Section>{$section}</Section>
        {/foreach}
      </Source>
    {/foreach}
  </Sources>
  {foreach from=$sections key=sectionName item=section}
    <Section name="{$sectionName}">
      {foreach from=$section item=abbrev}
        <Abbrev short="{$abbrev.short}"{if $abbrev.ambiguous
           } ambiguous="1"{/if}>{$abbrev.long}</Abbrev>
      {/foreach}
    </Section>
  {/foreach}
</AbbrevList>
