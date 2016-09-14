{extends "base.tpl"}

{block "before-content"}
  {block "search"}
    {include file="bits/searchForm.tpl"}
  {/block}
  {if !$suggestNoBanner && $skinVariables.banner}
    {block "banner"}
      {include file="bits/banner.tpl" id="otherPages"}
    {/block}
  {/if}
{/block}

{block "footer"}
  {if $skinVariables.fbLarge}
    <hr />
    {include file="bits/facebook.tpl"}
    <hr />
  {/if}
{/block}
