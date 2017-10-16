{extends "layout-admin.tpl"}

{block "title"}Căutare lexeme{/block}

{block "content"}

  <h3>
    {$count} rezultate
    {if $count > count($lexems)}
      (maximum {$lexems|count} afișate)
    {/if}
  </h3>

  {include "bits/lexemList.tpl"}

{/block}
