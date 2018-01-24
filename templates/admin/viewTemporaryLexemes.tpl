{extends "layout-admin.tpl"}

{block "title"}Lexeme neetichetate{/block}

{block "content"}

  <h3>{$lexemes|count} lexeme neetichetate</h3>

  {include "bits/lexemeList.tpl"}

{/block}
