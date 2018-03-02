<?xml version="1.0" encoding="UTF-8"?>
<AbbrevList>
  {foreach $map as $sourceId => $abbrList}
    <Source id="{$sourceId}">
    {foreach $abbrList as $short => $abbrev}
      {strip}
      <Abbrev short="{$short}"{if $abbrev.ambiguous} ambiguous="1"{/if}>
        {$abbrev.internalRep}
      </Abbrev>
        {/strip}
    {/foreach}
    </Source>
  {/foreach}
</AbbrevList>
