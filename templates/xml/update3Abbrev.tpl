<?xml version="1.0" encoding="UTF-8"?>
<AbbrevList>
  <Sources>
    {foreach from=$abbrev.sources key=sourceId item=sections}
      <Source id="{$sourceId}" sections="{$sections}"/>
    {/foreach}
  </Sources>
  {foreach from=$abbrev key=sectionName item=section}
    {if $sectionName != 'sources'}
      <Section name="{$sectionName}">
        {foreach from=$section key=from item=to}
          <Abbrev short="{$from}" long="{$to}"/>
        {/foreach}
      </Section>
    {/if}
  {/foreach}
</AbbrevList>
