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

  {include "paradigm/paradigm.tpl" lexeme=$exponent}

  <h3>Cuvinte care se flexionează conform acestui model (maximum 100 afișate)</h3>

  {include "search/lexemeList.tpl"}

  <p>
    <a class="btn btn-default" href="../modele-flexiune/{$model->modelType}">
      <i class="glyphicon glyphicon-chevron-left"></i>
      toate modelele de tip {$model->modelType}
    </a>
    {if User::can(User::PRIV_EDIT)}
      <a class="btn btn-default" href="../admin/editModel.php?id={$model->id}">
        <i class="glyphicon glyphicon-pencil"></i>
        editează modelul
      </a>
    {/if}
  </p>

{/block}
