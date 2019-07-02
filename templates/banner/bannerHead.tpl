{* Banner-related code that goes in the <head> *}

{if Util::isBannerVisible()}
  {if Config::BANNER_TYPE == 'pubgalaxy'}
    {include "banner/pubGalaxyHead.tpl"}
  {/if}
{/if}
