{extends file="admin/layout.tpl"}

{block name=title}Căutare lexeme{/block}

{block name=headerTitle}
  Căutare lexeme
  ({$lexems|count})
{/block}

{block name=content}
  {include file="admin/lexemList.tpl"}
{/block}
