{extends "layout-admin.tpl"}

{block "title"}Căutare lexeme{/block}

{block "content"}

  <h3>{$lexems|count} rezultate</h3>

  {include "admin/lexemList.tpl"}

{/block}
