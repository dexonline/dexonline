{extends "layout.tpl"}

{block "title"}{t}Flexion model{/t}{/block}

{block "content"}
  <h3>
    {t}Flexion model{/t} {$model} ({$exponent})
  </h3>

  {if $model->description}
    <h4>
      {t}Description{/t}: {$model->description}
    </h4>
  {/if}

  <div class="mb-3">
    {include "paradigm/paradigm.tpl" lexeme=$exponent}
  </div>

  <h3>{t}Words that inflect according to this model (maximum 100 displayed){/t}</h3>

  {include "search/lexemeList.tpl"}

  <p>
    {if User::can(User::PRIV_EDIT)}
      <a class="btn btn-primary" href="{Router::link('model/edit')}?id={$model->id}">
        {include "bits/icon.tpl" i=edit}
        {t}edit{/t}
      </a>
    {/if}
    <a class="btn btn-link" href="{Router::link('model/list')}/{$model->modelType}">
      {include "bits/icon.tpl" i=arrow_back}
      {t 1=$model->modelType}all models of type %1{/t}
    </a>
  </p>

{/block}
