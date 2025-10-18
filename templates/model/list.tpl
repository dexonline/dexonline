{extends "layout.tpl"}

{block "title"}{t}Flexion model{/t}{/block}

{block "content"}
  <h3>
    {t 1=$modelType->code 2=$modelType->description}Flexion models for the type %1 (%2){/t}
  </h3>

  <p>
    <a class="btn btn-link" href="{Router::link('games/scrabble')}">
      {include "bits/icon.tpl" i=arrow_back}
      {t}back{/t}
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
