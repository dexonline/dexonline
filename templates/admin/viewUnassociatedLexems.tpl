{extends "layout-admin.tpl"}

{block "title"}Lexeme neasociate{/block}

{block "content"}

  <h3>{$lexems|count} lexeme neasociate</h3>
  
  {include "bits/lexemList.tpl"}

{/block}
