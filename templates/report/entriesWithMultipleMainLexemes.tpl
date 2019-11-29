{extends "layout-admin.tpl"}

{block "title"}Intrări cu multiple lexeme principale{/block}

{block "content"}

  <h3>{$entries|count} {$prep|strip} intrări cu mai multe lexeme principale</h3>

  {include "bits/adminEntryCompositeTable.tpl"}

{/block}
