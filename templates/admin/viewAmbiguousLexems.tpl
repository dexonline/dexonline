{extends "layout-admin.tpl"}

{block "title"}Lexeme ambigue{/block}

{block "content"}

  <h3>{$lexems|count} lexeme ambigue (cu nume și descriere identice)</h3>
  
  {include "admin/lexemList.tpl"}

{/block}
