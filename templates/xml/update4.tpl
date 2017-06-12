{assign var="serveFullDump" value=$serveFullDump|default:false}
<?xml version="1.0" encoding="UTF-8"?>
{if isset($noFullDump)}
  <Error>
    Eroare: nu există niciun set complet de date. Probabil această eroare este tranzitorie, dar
    ne puteți contacta pentru detalii.
  </Error>
{else}
  <Files>
    <Full date="{$lastDump}">
      <Abbrevs>{$url}/{$lastDump}-abbrevs.xml.gz</Abbrevs>
      <Inflections>{$url}/{$lastDump}-inflections.xml.gz</Inflections>
      <Sources>{$url}/{$lastDump}-sources.xml.gz</Sources>
      {if $serveFullDump}
        <Definitions>{$url}/{$lastDump}-definitions.xml.gz</Definitions>
        <Lexems>{$url}/{$lastDump}-lexems.xml.gz</Lexems>
        <LexemDefinitionMap>{$url}/{$lastDump}-ldm.xml.gz</LexemDefinitionMap>
      {/if}
    </Full>
    <Diffs>
      {foreach $diffs as $date}
        <Diff date="{$date}">
          <Definitions>{$url}/{$date}-definitions-diff.xml.gz</Definitions>
          <Lexems>{$url}/{$date}-lexems-diff.xml.gz</Lexems>
          <LexemDefinitionMap>{$url}/{$date}-ldm-diff.xml.gz</LexemDefinitionMap>>
        </Diff>
      {/foreach}
    </Diffs>
  </Files>
{/if}
