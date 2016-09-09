<?xml version="1.0" encoding="UTF-8"?>
<AbbrevList>
  <Sources>
    {foreach $sources as $sourceId => $source}
      <Source id="{$sourceId}">
        {foreach $source as $section}
          <Section>{$section}</Section>
        {/foreach}
      </Source>
    {/foreach}
  </Sources>
  {foreach $sections as $sectionName => $section}
    <Section name="{$sectionName}">
      {foreach $section as $abbrev}
        <Abbrev short="{$abbrev.short}"{if $abbrev.ambiguous
           } ambiguous="1"{/if}>{$abbrev.long}</Abbrev>
      {/foreach}
    </Section>
  {/foreach}
</AbbrevList>
