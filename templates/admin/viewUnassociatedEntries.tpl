{extends file="layout-admin.tpl"}

{block name=title}Intrări neasociate{/block}

{block name=content}

  <h3>{$entries|count} intrări neasociate cu definiții</h3>

  {include file="admin/entryList.tpl"}

{/block}
