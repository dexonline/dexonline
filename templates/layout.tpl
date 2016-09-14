{extends "base.tpl"}

{block "before-content"}
  {block "search"}
    {include "bits/searchForm.tpl"}
  {/block}
  {if !$suggestNoBanner && $skinVariables.banner}
    {block "banner"}
      {include "bits/banner.tpl" id="otherPages"}
    {/block}
  {/if}
{/block}

{block "footer"}
  {if $skinVariables.fbLarge}
    <hr />
    {include "bits/facebook.tpl"}
    <hr />
  {/if}
{/block}
