{extends "layout-admin.tpl"}

{block name=title}Lexeme fără accent{/block}

{block name=content}

  <h3>{$lexems|count} lexeme fără accent</h3>

  {include "admin/lexemList.tpl"}

{/block}
