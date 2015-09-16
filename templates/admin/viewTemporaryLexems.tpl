{extends file="admin/layout.tpl"}

{block name=title}Lexeme neetichetate{/block}

{block name=headerTitle}
  Lexeme neetichetate
  ({$lexems|count})
{/block}

{block name=content}
  {include file="admin/lexemList.tpl"}
{/block}
