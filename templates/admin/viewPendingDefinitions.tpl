{extends file="admin/layout.tpl"}

{block name=title}Definiții nemoderate{/block}

{block name=headerTitle}
  Definiții nemoderate ({$searchResults|count})
{/block}

{block name=content}
  {include file="admin/definitionList.tpl"}
{/block}
