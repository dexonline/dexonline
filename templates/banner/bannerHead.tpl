{* Banner-related code that goes in the <head> *}

{if !$suggestNoBanner && empty($adult)}
  {if $cfg.banner.type == 'pubgalaxy'}
    {include "banner/pubGalaxyHead.tpl"}
  {/if}
{/if}
