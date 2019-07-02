{* Banner-related code that goes in the ad spot *}
{if Util::isBannerVisible()}
  <section class="row banner-section" data-placement="{Config::BANNER_PLACEMENT}">
    <div class="center-block text-center">
      {if Config::BANNER_TYPE == 'revive'}
        {include "banner/revive.tpl"}
      {elseif Config::BANNER_TYPE == 'adsense'}
        {include "banner/adsense.tpl"}
      {elseif Config::BANNER_TYPE == 'dfp'}
        {include "banner/dfp.tpl"}
      {elseif Config::BANNER_TYPE == 'pubgalaxy'}
        {include "banner/pubGalaxy.tpl"}
      {elseif Config::BANNER_TYPE == 'fake'}
        <div class="center-block fakeBanner">
          Banner fals
        </div>
      {/if}
    </div>
  </section>
{/if}
