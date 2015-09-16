{extends file="admin/layout.tpl"}

{block name=title}
  Lexeme pentru modelul {$modelType}{$modelNumber}
{/block}

{block name=headerTitle}
  Lexeme pentru modelul {$modelType}{$modelNumber}
  ({$lexems|count})
{/block}

{block name=content}
  {include file="admin/lexemList.tpl"}
{/block}
