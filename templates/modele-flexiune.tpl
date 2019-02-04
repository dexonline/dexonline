{extends "layout.tpl"}

{block "title"}Modele de flexiune{/block}

{block "content"}
  <h3>
    Modele de flexiune pentru tipul {$modelType->code} ({$modelType->description})
  </h3>

  <p>
    <a class="btn btn-default" href="../scrabble">
      <i class="glyphicon glyphicon-chevron-left"></i>
      înapoi
    </a>
  </p>

  {foreach $models as $i => $m}
    {assign var="l" value=$lexemes[$i]}
    <h4>
      {$m->number}. {$m->getHtmlExponent()}
    </h4>
    {include "paradigm/paradigm.tpl" lexeme=$l}
  {/foreach}
{/block}
