{extends file="layout.tpl"}

{block name=title}Modele de flexiune{/block}

{block name=content}
  <h3>
    Modele de flexiune pentru tipul {$modelType->code} ({$modelType->description})
    în LOC versiunea {$locVersion}
  </h3>

  <p>
    <a class="btn btn-default" href="scrabble">
      <i class="glyphicon glyphicon-chevron-left"></i>
      înapoi
    </a>
  </p>

  {foreach from=$models item=m key=i}
    {assign var="lm" value=$lexemModels[$i]}
    <h4>
      {$m->number}. {$m->getHtmlExponent()}
    </h4>
    {include file="paradigm/paradigm.tpl" lexemModel=$lm}
  {/foreach}
{/block}
