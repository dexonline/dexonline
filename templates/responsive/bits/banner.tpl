{assign var="adsProvider" value=$adsProvider|default:null}
{assign var="adsProviderParams" value=$adsProviderParams|default:null}
{** Arguments: id, width and height. Expects corresponding values in the [skin-*] section of dex.conf. **}
<section class="row" id="banner_{$id}">
  <div class="col-md-12">
    {if $adsProvider == 'diverta'}
      {* TODO: edit openx.tpl to make this work *}
      {include file="bits/openx.tpl" zoneId="" params=$adsProviderParams}
    {elseif $cfg.banner.type == 'openx'}
      {assign var="key" value="openx_`$id`"}
      {if $skinVariables.$key}
        {include file="bits/openx.tpl" zoneId=$skinVariables.$key}
      {/if}
    {elseif $cfg.banner.type == 'adsense'}
      {assign var="key" value="adsense_`$id`"}
      {if $skinVariables.$key}
        {include file="bits/adsense.tpl" adUnitId=$skinVariables.$key}
      {/if}
    {elseif $cfg.banner.type == 'fake'}
      <div class="center-block" style="background: #761818; color: white; margin-top: 25px; margin-bottom: 25px; font-size: 20px; height: {$height}px; max-width: {$width}px;">
        Banner fals
      </div>
    {/if}
  </div>
</section>
