{extends file="layout.tpl"}

{block name=title}Modele de flexiune{/block}

{block name=content}
  <p class="paragraphTitle">
    Modele de flexiune pentru tipul {$modelType->code} ({$modelType->description}) în LOC versiunea {$locVersion}
  </p>

  <a href="scrabble">« înapoi</a><br><br>

  {foreach from=$models item=m key=i}
    {assign var="lm" value=$lexemModels[$i]}
    <div class="scrabbleModelName">
      {$m->number}. {$m->exponent|regex_replace:"/\'(a|e|i|o|u|ă|î|â)/":"<span class=\"accented\">\$1</span>"}
    </div>
    {include file="paradigm/paradigm.tpl" lexemModel=$lm}
  {/foreach}
{/block}
