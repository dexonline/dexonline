{extends "layout-admin.tpl"}

{block "title"}Definiții neasociate{/block}

{block "content"}

  <h3>{$searchResults|count} definiții neasociate</h3>

  <div class="panel panel-default">
    <div class="panel-body panel-admin">
      {include file="admin/definitionList.tpl"}
    </div>
  </div>

{/block}
