<a class="widget wotd row" href="{Router::link('wotd/view')}/{$smarty.now|date_format:'%Y/%m/%d'}">
  <div class="col-lg-8 col-md-12 col-sm-12 col-xs-6">
    <h4>{t}word of the day{/t}</h4><br>
    {if $wotdDef}
      <span class="widget-value">{$wotdDef->lexicon}</span>
    {/if}
  </div>
  <div class="col-lg-4 col-md-12 col-sm-12 col-xs-6 widget-thumbnail">
    <img src="{$thumbUrl}" alt="iconiță cuvântul zilei" class="widget-icon">
  </div>
</a>
