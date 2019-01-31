{* Banner-related code that goes in the <head> *}

{if !$privateMode && !$suggestNoBanner && empty($adult) && !User::can(User::PRIV_ANY)}
  {if Config::BANNER_TYPE == 'pubgalaxy'}
   {include "banner/pubGalaxyHead.tpl"}
  {/if}
{/if}
