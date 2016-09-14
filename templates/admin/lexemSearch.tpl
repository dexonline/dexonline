{extends "layout-admin.tpl"}

{block name=title}Căutare lexeme{/block}

{block name=content}

  <h3>{$lexems|count} rezultate</h3>

  {include file="admin/lexemList.tpl"}

{/block}
