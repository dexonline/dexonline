{extends "layout-admin.tpl"}

{block "title"}Lexeme fără accent{/block}

{block "content"}

  <h3>{$lexems|count} lexeme fără accent</h3>

  {include "admin/lexemList.tpl"}

{/block}
