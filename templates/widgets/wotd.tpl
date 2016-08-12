<a class="widget wotd row" href="{$wwwRoot}cuvantul-zilei/{$today}">
  <div class="col-md-8">
    <h4>Cuvântul zilei</h4><br/>
    {if $wotdDef}
      <span class="widget-value">{$wotdDef->lexicon}</span>
    {/if}
  </div>
  <div class="col-md-4">
  {if !$thumbUrl}
    {assign var="thumbUrl" value="wotd/thumb/generic.jpg"}
  {/if}
  <img src="{$thumbUrl}" alt="iconiță cuvântul zilei" class="widget-icon">
  </div>
</a>
