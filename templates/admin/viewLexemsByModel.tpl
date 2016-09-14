{extends "layout-admin.tpl"}

{block "title"}
  Lexeme pentru modelul {$modelType}{$modelNumber}
{/block}

{block "content"}
  <h3>{$lexems|count} lexeme pentru modelul {$modelType}{$modelNumber}</h3>
  
  {include file="admin/lexemList.tpl"}
{/block}
