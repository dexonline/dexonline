{extends file="admin/layout.tpl"}

{block name=title}Lexeme neasociate{/block}

{block name=headerTitle}
  Lexeme neasociate cu definiții
  ({$lexems|count})
{/block}

{block name=content}
  {include file="admin/lexemList.tpl"}
{/block}
