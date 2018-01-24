{extends "layout-admin.tpl"}

{block "title"}Lexeme fără accent{/block}

{block "content"}

  <h3>{$lexemes|count} lexeme fără accent</h3>

  {include "bits/lexemList.tpl"}

{/block}
