<div class="panel-admin">
  <div class="panel panel-default">
    <div class="panel-heading" id="panel-heading">
      Lista intrărilor cu multiple lexeme principale
    </div>
    <div class="text-muted">
      tabelul s-ar putea încărca foarte greu, aveți răbdare
    </div>
    {if $entries|count != 0}
      <table id="entries" class="table">
          <thead>
            <tr>
              <th>Descriere</th>
              <th>Listă lexeme</th>
              <th class="text-right">Modele</th>
              <th class="text-right">Modificat</th>
              <th class="text-right">la data</th>
            </tr>
          </thead>
        <tbody>
          {foreach $entries as $e}
            {include "bits/entryRow.tpl"}
          {/foreach}
        </tbody>
        {include "bits/pager.tpl" id="entriesPager" colspan="5"}
      </table>
    {else}
      <p class="panel-body text-danger">
        Nu există intrări.
      </p>
    {/if}
    <div class="panel-footer text-center clearfix">
      <span class="label label-default">Total intrări: <span id="entryCount">{$entries|count}</span></span>
    </div>
  </div>
</div>
