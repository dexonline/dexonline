{extends file="admin/layout.tpl"}

{block name=title}Intrări neasociate{/block}

{block name=headerTitle}
  Intrări neasociate cu definiții
  ({$entries|count})
{/block}

{block name=content}
  {include file="admin/entryList.tpl"}
{/block}
