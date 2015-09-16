{extends file="admin/layout.tpl"}

{block name=title}Definiții neasociate{/block}

{block name=headerTitle}
  Definiții neasociate ({$searchResults|count})
{/block}

{block name=content}
  {include file="admin/definitionList.tpl"}
{/block}
