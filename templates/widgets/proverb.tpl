<a
  href="{Router::link('proverb/view')}/{$proverbId}"
  class="widget wotd d-flex flex-md-column flex-xl-row">
  <div class="flex-grow-1">
    <h4>Proverbe</h4><br>
    {if $proverbTitle}
      <span class="widget-value">{$proverbTitle}</span>
    {/if}
  </div>
  <div>
    <img src="{$thumbProverbUrl}" onerror="this.onerror=null;this.src='{$thumbProverbDefault}'" alt="iconiță proverbe" class="widget-icon">
  </div>
</a>
