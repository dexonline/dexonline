{extends file="admin/layout.tpl"}

{block name=title}Lexeme ambigue{/block}

{block name=headerTitle}
  Lexeme ambigue (cu nume și descriere identice)
  ({$lexems|count})
{/block}

{block name=content}
  {include file="admin/lexemList.tpl"}
{/block}
