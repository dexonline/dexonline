{extends "layout-admin.tpl"}

{block "title"}Clonare model{/block}

{block "content"}
  <h3>Clonare model {$modelType}{$modelNumber}</h3>

  <form method="post">
    <input type="hidden" name="modelType" value="{$modelType}">
    <input type="hidden" name="modelNumber" value="{$modelNumber}">

    <div class="mb-3 row row-cols-lg-auto g-3 align-items-center">
      <div class="col=12">
        <label class="col-form-label">număr nou de model</label>
      </div>
      <div class="col=12">
        <input
          type="text"
          class="form-control"
          name="newModelNumber"
          value="{$newModelNumber|escape}">
      </div>
    </div>

    <p>
      Bifați lexemele pe care doriți să le migrați la noul model:
    </p>

    <div class="mb-3">
      <button id="checkAll" class="btn btn-light" type="button">
        {include "bits/icon.tpl" i=done}
        bifează/debifează tot
      </button>
    </div>

    <div class="row mx-1 mb-3">
      {foreach $lexemes as $l}
        {capture "label"}
        {include "bits/lexemeName.tpl" lexeme=$l}
        <span class="form-text">({$l->modelType}{$l->modelNumber})</span>
        {/capture}

        {include "bs/checkbox.tpl"
          name='lexemeId[]'
          label=$smarty.capture.label
          divClass='col-6 col-sm-4 col-md-3 col-lg-2'
          value=$l->id}
      {/foreach}
    </div>

    <div>
      <button type="submit" class="btn btn-primary" name="saveButton">
        {include "bits/icon.tpl" i=save}
        <u>s</u>alvează
      </button>
    </div>
  </form>
{/block}
