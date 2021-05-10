{if $entries|count != 0}
  <table id="entries" class="table table-striped table-bordered" role="grid">
    <thead>
      <tr>
        <th scope="col" data-column-id="description">descriere</th>
        <th class="text-center" scope="col" data-column-id="lexemes">bifă</th>
        <th scope="col" data-column-id="lexemes">lexeme</th>
        <th class="text-right" scope="col" data-column-id="modified">modificată</th>
        <th class="text-right" scope="col" data-column-id="modDate">la data</th>
      </tr>
    </thead>
    <tbody>
      {foreach $entries as $e}
        {include "bits/entryRow.tpl"}
      {/foreach}
    </tbody>
  </table>
{else}
  <p class="panel-body text-danger">
    Nu există intrări.
  </p>
{/if}

<script>
  $(document).ready(function() {
    $('#entries').tablesorter();
  });
</script>
