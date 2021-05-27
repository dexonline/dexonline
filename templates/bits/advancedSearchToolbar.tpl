<div class="text-center d-flex align-items-center justify-content-between">
  <button
    type="submit"
    class="btn btn-light {if $stats.page == 1}invisible{/if}"
    name="prevPageButton">
    {include "bits/icon.tpl" i=chevron_left}
  </button>

  {if $stats.numPages > 1}
    rezultatele {$stats.firstResult}-{$stats.lastResult} din {$stats.numResults}
    (pagina {$stats.page} din {$stats.numPages})
  {else}
    {$stats.numResults} (de) rezultate
  {/if}

  <button
    type="submit"
    class="btn btn-light {if $stats.page == $stats.numPages}invisible{/if}"
    name="nextPageButton">
    {include "bits/icon.tpl" i=chevron_right}
  </button>
</div>
