{extends file="layout-admin.tpl"}

{block name=title}
  Lexeme pentru modelul {$modelType}{$modelNumber}
{/block}

{block name=content}
  <h3>{$lexems|count} lexeme pentru modelul {$modelType}{$modelNumber}</h3>
  
  {include file="admin/lexemList.tpl"}
{/block}
