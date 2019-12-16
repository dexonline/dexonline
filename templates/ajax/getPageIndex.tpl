<div class="panel-admin">
  <div class="panel panel-default">
    <div class="panel-heading" id="panel-heading">
      Lista indecșilor de pagină
    </div>
    {if $results|count  != 0}
      <table id="table-page-index" class="table table-condensed table-striped table-bordered">
        <thead>
        <tr>
          <th data-column-id="id" data-type="numeric" data-identifier="true">Id</th>
          <th data-column-id="volume">Volum</th>
          <th data-column-id="page">Pagină</th>
          <th data-column-id="word">Intrare</th>
          <th data-column-id="number">Nr. def.</th>
          {if User::can(User::PRIV_ADMIN)}
            <th data-column-id="commands" data-formatter="commands" data-sortable="false">Comenzi</th>
          {/if}
        </tr>
        </thead>
        <tbody>
        {foreach $results as $row}
          {include "bits/pageIndexRow.tpl"}
        {/foreach}
        </tbody>
      </table>
    {else}
      <p class="panel-body text-danger">
        Nu există indecși de pagină pentru dicționarul ales.
      </p>
    {/if}
    <div class="panel-footer text-center clearfix">
      <span class="label label-default">Total indecși: <span id="pageIndexCount">{$results|count}</span></span>
    </div>
  </div>
</div>
{if $results|count != 0 && User::can(User::PRIV_ADMIN)}
  <div class="pull-right">
    <button type="button" class="btn btn-primary" id="command-add" data-source-id="{$sourceId}">
      <span class="glyphicon glyphicon-plus"></span> Adaugă
    </button>
  </div>
{/if}
