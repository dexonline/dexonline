{extends file="admin/layout.tpl"}

{block name=title}Lexeme fără accent{/block}

{block name=headerTitle}
  Lexeme fără accent
  ({$lexems|count})
{/block}

{block name=content}
  {include file="admin/lexemList.tpl"}
{/block}
