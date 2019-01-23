{* Banner-related code that goes in the <head> *}

{if !$privateMode && !$suggestNoBanner && empty($adult) && !User::can(User::PRIV_ANY)}
  {if $cfg.banner.type == 'pubgalaxy'}
   {include "banner/pubGalaxyHead.tpl"}
  {/if}
{/if}
