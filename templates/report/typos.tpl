{extends "layout-admin.tpl"}

{block "title"}Definiții cu greșeli de tipar{/block}

{block "content"}
  <h3>Definiții cu greșeli de tipar</h3>

  <form class="form-horizontal">
    <div class="form-group">
      <label class="col-md-1 control-label">sursa</label>
      <div class="col-md-11">
        <div class="input-group">
          {include "bits/sourceDropdown.tpl" id=$sources.vars.id}
          <span id="load" class="input-group-addon ld-ext-left">
            <b id="count">{$searchResults|count}</b>
            <div class="ld ld-ring ld-spin-fast"></div>
          </span>
        </div>
      </div>
    </div>
  </form>

  <div id="typosPanelContent" class="voffset3">
    {*  results are displayed through ajax on sourceDropdown change *}
    {include "report/typosList.tpl"}
  </div>
{/block}
