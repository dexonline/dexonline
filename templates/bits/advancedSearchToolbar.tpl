<h3 class="panel-title text-center">
  {if $stats.page > 1}
    <button type="submit" class="btn btn-default pull-left" name="prevPageButton">
      <i class="glyphicon glyphicon-chevron-left"></i>
      înapoi
    </button>
  {/if}

  rezultatele {$stats.firstResult}-{$stats.lastResult} din {$stats.numResults}
  (pagina {$stats.page} din {$stats.numPages})

  {if $stats.page < $stats.numPages}
    <button type="submit" class="btn btn-default pull-right" name="nextPageButton">
      înainte
      <i class="glyphicon glyphicon-chevron-right"></i>
    </button>
  {/if}

  {* invisible button to give the heading a proper height *}
  <button type="button" class="btn invisible">x</button>
</h3>
