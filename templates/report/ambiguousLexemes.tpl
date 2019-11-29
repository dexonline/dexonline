{extends "layout-admin.tpl"}

{block "title"}Lexeme ambigue{/block}

{block "content"}

  <h3>{$lexemes|count} lexeme ambigue (cu nume și descriere identice)</h3>

  {include "bits/lexemeList.tpl"}

{/block}
