{extends "layout.tpl"}

{block "title"}Model de flexiune{/block}

{block "content"}
  <h3>
    Modelul de flexiune {$model} ({$exponent})
  </h3>

  {if $model->description}
    <h4>
      Descriere: {$model->description}
    </h4>
  {/if}

  <div class="mb-3">
    {include "paradigm/paradigm.tpl" lexeme=$exponent}
  </div>

  <h3>Cuvinte care se flexionează conform acestui model (maximum 100 afișate)</h3>

  {include "search/lexemeList.tpl"}

  <p>
    {if User::can(User::PRIV_EDIT)}
      <a class="btn btn-primary" href="{Router::link('model/edit')}?id={$model->id}">
        {include "bits/icon.tpl" i=edit}
        editează
      </a>
    {/if}
    <a class="btn btn-link" href="{Router::link('model/list')}/{$model->modelType}">
      {include "bits/icon.tpl" i=arrow_back}
      toate modelele de tip {$model->modelType}
    </a>
  </p>

{/block}
