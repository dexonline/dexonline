{assign var="adsProvider" value=$adsProvider|default:null}
{assign var="adsProviderParams" value=$adsProviderParams|default:null}
{** Arguments: id, width and height. Expects corresponding values in the [skin] section of dex.conf. **}
<section class="row topBanner" id="banner_{$id}">
  <div id="bannerWrapper" class="center-block text-center">
    {if $adsProvider == 'diverta'}
      {* TODO: edit revive.tpl to make this work *}
      {include "bits/revive.tpl" zoneId="" params=$adsProviderParams}
    {elseif $cfg.banner.type == 'revive'}
      {include "bits/revive.tpl"}
    {elseif $cfg.banner.type == 'adsense'}
      {assign var="key" value="adsense_`$id`"}
      {if $cfg.banner.$key}
        {include "bits/adsense.tpl" adUnitId=$cfg.banner.$key}
      {/if}
    {elseif $cfg.banner.type == 'pubgalaxy'}
      {include "bits/pubGalaxy.tpl"}
    {elseif $cfg.banner.type == 'fake'}
      <div class="center-block fakeBanner">
        Banner fals
      </div>
    {/if}
  </div>
</section>
