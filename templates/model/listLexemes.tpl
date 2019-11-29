{extends "layout-admin.tpl"}

{block "title"}
  Lexeme pentru modelul {$modelType}{$modelNumber}
{/block}

{block "content"}
  <h3>{$lexemes|count} lexeme pentru modelul {$modelType}{$modelNumber}</h3>

  {include "bits/lexemeList.tpl"}
{/block}
