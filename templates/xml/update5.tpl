{assign var="serveFullDump" value=$serveFullDump|default:false}
<?xml version="1.0" encoding="UTF-8"?>
<Files>
  <Full date="{$lastDump}">
    <Abbrevs>{$url}/{$lastDump}-abbrevs.xml.gz</Abbrevs>
    <Inflections>{$url}/{$lastDump}-inflections.xml.gz</Inflections>
    <Sources>{$url}/{$lastDump}-sources.xml.gz</Sources>
    {if $serveFullDump}
      <Definitions>{$url}/{$lastDump}-definitions.xml.gz</Definitions>
      <Entries>{$url}/{$lastDump}-entries.xml.gz</Entries>
      <Lexems>{$url}/{$lastDump}-lexems.xml.gz</Lexems>
      <EntryDefinitionMap>{$url}/{$lastDump}-edm.xml.gz</EntryDefinitionMap>
    {/if}
  </Full>
  <Diffs>
    {foreach from=$diffs item=date}
    <Diff date="{$date}">
      <Definitions>{$url}/{$date}-definitions-diff.xml.gz</Definitions>
      <Entries>{$url}/{$date}-entries-diff.xml.gz</Entries>
      <Lexems>{$url}/{$date}-lexems-diff.xml.gz</Lexems>
      <EntryDefinitionMap>{$url}/{$date}-edm-diff.xml.gz</EntryDefinitionMap>
    </Diff>
    {/foreach}
  </Diffs>
</Files>
