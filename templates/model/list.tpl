{extends "layout.tpl"}

{block "title"}Modele de flexiune{/block}

{block "content"}
  <h3>
    Modele de flexiune pentru tipul {$modelType->code} ({$modelType->description})
  </h3>

  <p>
    <a class="btn btn-link" href="{Router::link('games/scrabble')}">
      {include "bits/icon.tpl" i=arrow_back}
      înapoi
    </a>
  </p>

  {foreach $models as $i => $m}
    <div class="mb-3">
      {assign var="l" value=$lexemes[$i]}
      <h4>
        {$m->number}. {$m->getHtmlExponent()}
      </h4>
      {include "paradigm/paradigm.tpl" lexeme=$l}
    </div>
  {/foreach}
{/block}
