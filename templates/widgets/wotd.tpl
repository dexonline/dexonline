<a class="widget wotd row" href="{$wwwRoot}cuvantul-zilei/{$today}">
  <div class="col-lg-8 col-md-12 col-sm-12 col-xs-6">
    <h4>{'Word of the day'|_}</h4><br>
    {if $wotdDef}
      <span class="widget-value">{$wotdDef->lexicon}</span>
    {/if}
  </div>
  <div class="col-lg-4 col-md-12 col-sm-12 col-xs-6 widget-thumbnail">
    <img src="{$thumbUrl}" alt="iconiță cuvântul zilei" class="widget-icon">
  </div>
</a>
