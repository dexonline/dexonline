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

  <p>
    <a class="btn btn-default" href="#" onclick="window.history.back();">
      <i class="glyphicon glyphicon-chevron-left"></i>
      înapoi
    </a>
  </p>

  {include "paradigm/paradigm.tpl" lexeme=$exponent}

  <h3>Cuvinte care se flexionează conform acestui model (maxim 100 afișate)</h3>

  {include "search/lexemeList.tpl"}
{/block}
