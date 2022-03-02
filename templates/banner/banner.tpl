{* Banner-related code that goes in the ad spot *}
{if Util::isBannerVisible()}
  <section class="banner-section" data-placement="{Config::BANNER_PLACEMENT}">
    {if Config::BANNER_TYPE == 'revive'}
      {include "banner/revive.tpl"}
    {elseif Config::BANNER_TYPE == 'dfp'}
      {include "banner/dfp.tpl"}
    {elseif Config::BANNER_TYPE == 'fake'}
      <div class="fakeBanner">
        Banner fals
      </div>
    {/if}
  </section>
{/if}
