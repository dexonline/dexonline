<div class="panel-admin">
  <div class="panel panel-default">
    <div class="panel-heading" id="panel-heading">
      Lista intrărilor cu multiple lexeme principale
    </div>
    {if $entries|count != 0}
      <table id="entries" class="table table-striped table-bordered" role="grid">
          <thead>
            <tr>
              <th scope="col" data-column-id="description">Descriere</th>
              <th scope="col" data-column-id="lexemes">Listă lexeme</th>
              <th class="text-right" scope="col" data-column-id="modele">Modele</th>
              <th class="text-right" scope="colgroup" data-column-id="modified">Modificat</th>
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
    <div class="panel-footer text-center clearfix">
      <span class="label label-default">Total intrări afișate: <span id="entryCount">{$entries|count}</span></span>
    </div>
  </div>
</div>

<script>
  $(document).ready(function() {
    $('#entries').tablesorter();
  });
</script>
