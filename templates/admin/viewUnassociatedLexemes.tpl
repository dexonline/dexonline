{extends "layout-admin.tpl"}

{block "title"}Lexeme neasociate{/block}

{block "content"}

  <h3>{$lexemes|count} lexeme neasociate</h3>
  
  {include "bits/lexemList.tpl"}

{/block}
