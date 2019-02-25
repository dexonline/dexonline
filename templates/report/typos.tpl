{extends "layout-admin.tpl"}

{block "title"}Definiții cu greșeli de tipar{/block}

{block "content"}
  <h3>{$searchResults|count} definiții cu greșeli de tipar</h3>

  <form class="form-horizontal">
    <div class="form-group">
      <label class="col-md-1 control-label">sursa</label>
      <div class="col-md-11">
        {include "bits/sourceDropDown.tpl" urlName=1 autosubmit=1}
      </div>
    </div>
  </form>

  <div class="voffset3"></div>

  {foreach $searchResults as $row}
    {include "bits/definition.tpl"
    showHistory=1
    showStatus=1
    showTypos=1}
  {/foreach}

{/block}
