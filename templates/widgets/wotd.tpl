<a
  href="{Router::link('wotd/view')}/{$smarty.now|date_format:'%Y/%m/%d'}"
  class="widget wotd d-flex flex-md-column flex-xl-row">
  <div class="flex-grow-1">
    <h4>{t}word of the day{/t}</h4><br>
    {if $wotdDef}
      <span class="widget-value">{$wotdDef->lexicon}</span>
    {/if}
  </div>
  <div>
    <img src="{$thumbUrl}" alt="iconiță cuvântul zilei" class="widget-icon">
  </div>
</a>
