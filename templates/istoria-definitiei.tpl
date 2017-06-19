{extends "layout.tpl"}

{block "title"}Istoria definiției {$def->lexicon}{/block}

{block "content"}
  <h3>Istoria definiției <a href="{$wwwRoot}definitie/{$def->id}">{$def->lexicon}</a></h3>

  {foreach $changeSets as $c}
    {include "bits/definitionChange.tpl"}
  {foreachelse}
    <p>
      Nu există modificări la această definiție (sau ultimele modificări s-au întâmplat
      înainte să începem să stocăm istoricul definițiilor).
    </p>
  {/foreach}
{/block}
