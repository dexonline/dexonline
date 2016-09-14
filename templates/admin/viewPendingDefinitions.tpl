{extends "layout-admin.tpl"}

{block name=title}Definiții nemoderate{/block}

{block name=content}

  <h3>{$searchResults|count} definiții nemoderate</h3>

  <div class="panel panel-default">
    <div class="panel-body panel-admin">
      {include file="admin/definitionList.tpl"}
    </div>
  </div>

{/block}
