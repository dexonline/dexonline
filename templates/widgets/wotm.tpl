{extends file="widgets/layout.tpl"}

{block name="widget-header"}
  Cuvântul lunii
{/block}

{block name="widget-body"}
  <img src="{$thumbUrlM}" alt="iconiță cuvântul lunii" class="commonShadow" />
  {if $wotmDef}
    {include file="bits/wotmurl.tpl" linkText=$wotmDef->lexicon}
  {/if}
{/block}
