{extends "layout-admin.tpl"}

{block "title"}Definiții cu greșeli de tipar{/block}

{block "content"}
  <h3>{$searchResults|count} definiții cu greșeli de tipar</h3>

  <form class="row row-cols-sm-auto g-2 align-items-center mb-3">
    <div class="col-12">
      <label class="col-form-label">sursa</label>
    </div>
    <div class="col-12 w-50">
      {include "bits/sourceDropDown.tpl" urlName=1 autosubmit=1}
    </div>
  </form>

  {foreach $searchResults as $row}
    {include "bits/definition.tpl"
    showHistory=1
    showStatus=1
    showTypos=1}
  {/foreach}

{/block}
