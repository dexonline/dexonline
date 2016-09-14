{extends "layout-admin.tpl"}

{block "title"}Definiții cu greșeli de tipar{/block}

{block "content"}
  <h3>{$searchResults|count} definiții cu greșeli de tipar</h3>

  <form class="form-inline">
    <div class="form-group">
      <label class="control-label">sursa</label>
      {include "bits/sourceDropDown.tpl" urlName=1 autosubmit=1}
    </div>
  </form>

  <div class="voffset3"></div>

  <div class="panel panel-default">
    <div class="panel-body panel-admin">
      {include "admin/definitionList.tpl"}
    </div>
  </div>
{/block}
