{assign var="withCharmap" value=false scope=parent}
{extends "layout-admin.tpl"}

{block "title"}Intrări cu discrepanțe la lexemele principale{/block}

{block "content"}

  <h3>
    Intrări cu discrepanțe la lexemele principale
    {if count($entries) == $numEntries}
      ({$numEntries})
    {else}
      ({$entries|count} din {$numEntries} afișate)
    {/if}
  </h3>

  <p>
    Sunt listate intrările pentru care numărul de lexeme principale și bifa
    „lexeme principale multiple” sunt în dezacord.
  </p>

  {include "bits/adminEntryCompositeTable.tpl"}

{/block}
