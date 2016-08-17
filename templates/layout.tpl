{extends file="base.tpl"}

{block name="before-content"}
  {if !$suggestHiddenSearchForm && $skinVariables.searchForm}
    {include file="bits/searchForm.tpl"}
  {/if}
  {if !$suggestNoBanner && $skinVariables.banner}
    {include file="bits/banner.tpl" id="otherPages"}
  {/if}
{/block}

{block name="footer"}
  {if $skinVariables.fbLarge}
    <hr />
    {include file="bits/facebook.tpl"}
    <hr />
  {/if}
{/block}
