<a href="{$wwwRoot}cuvantul-lunii/{$todayM}" class="widget wotm row">
  <div class="col-lg-8 col-md-12 col-sm-12 col-xs-6">
    <h4>{t}word of the month{/t}</h4><br>
    {if $wotmDef}
      <span class="widget-value">{$wotmDef->lexicon}</span>
    {/if}
  </div>
  <div class="col-lg-4 col-md-12 col-sm-12 col-xs-6 widget-thumbnail">
    <img src="{$thumbUrlM}" alt="iconiță cuvântul lunii" class="widget-icon">
  </div>
</a>
