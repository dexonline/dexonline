{extends file="base.tpl"}

{block name="before-content"}
  {block name="search"}
    {include file="bits/searchForm.tpl"}
  {/block}
  {if !$suggestNoBanner && $skinVariables.banner}
    {block name="banner"}
      {include file="bits/banner.tpl" id="otherPages"}
    {/block}
  {/if}
{/block}

{block name="footer"}
  {if $skinVariables.fbLarge}
    <hr />
    {include file="bits/facebook.tpl"}
    <hr />
  {/if}
{/block}
