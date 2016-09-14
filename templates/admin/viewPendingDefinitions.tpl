{extends "layout-admin.tpl"}

{block "title"}Definiții nemoderate{/block}

{block "content"}

  <h3>{$searchResults|count} definiții nemoderate</h3>

  <div class="panel panel-default">
    <div class="panel-body panel-admin">
      {include "admin/definitionList.tpl"}
    </div>
  </div>

{/block}
