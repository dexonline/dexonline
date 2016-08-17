{assign var="adsProvider" value=$adsProvider|default:null}
{assign var="adsProviderParams" value=$adsProviderParams|default:null}
{** Arguments: id, width and height. Expects corresponding values in the [skin] section of dex.conf. **}
<section class="row" id="banner_{$id}" style="margin-bottom: 25px;">
  <div id="bannerWrapper" class="center-block">
    {if $adsProvider == 'diverta'}
      {* TODO: edit revive.tpl to make this work *}
      {include file="bits/revive.tpl" zoneId="" params=$adsProviderParams}
    {elseif $cfg.banner.type == 'revive'}
      {include file="bits/revive.tpl"}
    {elseif $cfg.banner.type == 'adsense'}
      {assign var="key" value="adsense_`$id`"}
      {if $cfg.banner.$key}
        {include file="bits/adsense.tpl" adUnitId=$cfg.banner.$key}
      {/if}
    {elseif $cfg.banner.type == 'fake'}
      <div class="center-block fakeBanner">
        Banner fals
      </div>
    {/if}
  </div>
</section>
