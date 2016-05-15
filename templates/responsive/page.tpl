{extends file="responsive/responsive-layout.tpl"}

{block name="before-content"}
  {if !$suggestHiddenSearchForm && $skinVariables.searchForm}
    {include file="responsive/bits/searchForm.tpl"}
  {/if}
  {if !$suggestNoBanner && $skinVariables.banner}
    {include file="responsive/bits/banner.tpl" id="otherPages" width="100%" height="90"}
  {/if}
{/block}

{block name="after-content"}
  {if $skinVariables.fbLarge}
    {include file="bits/facebook.tpl"}
  {/if}
{/block}
