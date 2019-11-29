{extends "layout-admin.tpl"}

{block "title"}Definiții cu glife rare, fără etichetă{/block}

{block "content"}
  <h3>Definiții cu glife rare, fără etichetă</h3>
  <form class="form-horizontal" method="post" enctype="multipart/form-data">
    <div class="panel panel-default">
      <div class="panel-heading clearfix">
        Alegere sursă
      </div>
      <div class="panel-body">
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
      </div>
    </div>

    <div class="panel-admin">
      <div class="panel panel-default">
        <div class="panel-heading clearfix" id="panel-heading">
          Lista definițiilor
          <div class="btn-group pull-right">
            <label id="labelAll" class="btn btn-xs btn-default">
              <input id="toggleAll" class="toggleAll bulk-checkbox" type="checkbox">
              <i class="glyphicon glyphicon-ok"></i>
            </label>
          </div>
        </div>
        <div class="panel-body">
          <div id="missingRareGlyphsTagContent" class="voffset3">
            {* div populated by ajax calls *}
            {include "bits/definitionList.tpl"}
          </div>
        </div>
        <div class="panel-footer clearfix">
          <div class="pull-right">
            <span id="chng">{$searchResults|count}</span>
            <span id="de">de</span>
            definiții vor fi etichetate
          </div>
        </div>
      </div>
    </div>
    <div>
      <button id="btnSave" type="submit" name="submit" class="btn btn-primary">
        <i class="glyphicon glyphicon-plus"></i>
        adaugă eticheta [{$tag->value}]
      </button>
    </div>
  </form>
{/block}
