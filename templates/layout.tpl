{extends file="base.tpl"}

{block name="before-content"}
  {if !$suggestHiddenSearchForm && $skinVariables.searchForm}
    {include file="bits/searchForm.tpl"}
  {/if}
  {if !$suggestNoBanner && $skinVariables.banner}
    {include file="bits/banner.tpl" id="otherPages" width="728" height="90"}
  {/if}
{/block}

{block name="after-content"}
  {if $skinVariables.fbLarge}
    {include file="bits/facebook.tpl"}
  {/if}
{/block}
