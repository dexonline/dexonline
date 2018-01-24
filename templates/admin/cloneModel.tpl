{extends "layout-admin.tpl"}

{block "title"}Clonare model{/block}

{block "content"}
  <h3>Clonare model {$modelType}{$modelNumber}</h3>

  <form method="post">
    <input type="hidden" name="modelType" value="{$modelType}">
    <input type="hidden" name="modelNumber" value="{$modelNumber}">

    <div class="form-group form-inline">
      <label class="control-label">număr nou de model</label>
      <input type="text" class="form-control" name="newModelNumber" value="{$newModelNumber|escape}">
    </div>

    <p>
      Bifați lexemele pe care doriți să le migrați la noul model:
    </p>

    <div class="form-group">
      <button class="btn btn-default bulk" type="button" data-checked="1">
        <i class="text-success glyphicon glyphicon-ok"></i>
        bifează tot
      </button>
      <button class="btn btn-default bulk" type="button" data-checked="0">
        <i class="text-danger glyphicon glyphicon-remove"></i>
        debifează tot
      </button>
    </div>

    <div class="row form-group">
      {foreach $lexemes as $l}
        <div class="checkbox col-xs-6 col-sm-4 col-md-3 col-lg-2">
          <label>
            <input type="checkbox" name="lexemeId[]" value="{$l->id}">
            {include "bits/lexemeName.tpl" lexem=$l}
            <small class="text-muted">({$l->modelType}{$l->modelNumber})</small>
          </label>
        </div>
      {/foreach}
    </div>

    <div class="form-group">
      <button type="submit" class="btn btn-success" name="saveButton">
        <i class="glyphicon glyphicon-floppy-disk"></i>
        <u>s</u>alvează
      </button>
    </div>
  </form>
{/block}
