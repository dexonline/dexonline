{extends file="widgets/layout.tpl"}

{block name="widget-header"}
  Cuvântul zilei
{/block}

{block name="widget-body"}
  {if !$thumbUrl}
    {assign var="thumbUrl" value="wotd/thumb/generic.jpg"}
  {/if}
  <img src="{$thumbUrl}" alt="iconiță cuvântul zilei" class="commonShadow" />
  {if $wotdDef}
    {include file="bits/wotdurl.tpl" linkText=$wotdDef->lexicon}
  {/if}
{/block}
