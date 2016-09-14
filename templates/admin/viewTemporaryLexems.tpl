{extends "layout-admin.tpl"}

{block name=title}Lexeme neetichetate{/block}

{block name=content}

  <h3>{$lexems|count} lexeme neetichetate</h3>

  {include file="admin/lexemList.tpl"}

{/block}
