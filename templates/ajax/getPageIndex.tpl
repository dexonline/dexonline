<div class="panel-admin">
  <div class="panel panel-default">
    <div class="panel-heading" id="panel-heading">
      Lista indexurilor de pagini
    </div>
    {if $results|count  != 0}
      <table id="table-pageindex" class="table table-condensed table-striped table-bordered">
        <thead>
          <tr>
            <th data-sorter="false">Id</th>
            <th>Vol.</th>
            <th>Pag.</th>
            <th style="width:50%">Cuvânt</th>
            <th data-sorter="false">Indice/Exponent</th>
            {if User::can(User::PRIV_ADMIN)}
              <th data-sorter="false">Comenzi</th>
            {/if}
          </tr>
        </thead>
        <tbody>
          {foreach $results as $row}
            {include "bits/pageindexRow.tpl"}
          {/foreach}
        </tbody>
        {include "bits/pager.tpl" id="pageindexPager" colspan="6"}
      </table>
    {else}
      <p class="panel-body text-danger">
        Nu există pagini încărcate pentu dicționarul ales.
      </p>
    {/if}
    {*<div class="panel-footer text-center clearfix">
      <span class="label label-default">Total înregistrări: <span id="pageindexCount">{$results|count}</span></span>
    </div>*}
  </div>
</div>
{if $results|count != 0 && User::can(User::PRIV_ADMIN)}
  <div class="pull-right">
    <button type="button" class="btn btn-primary" id="command-add" data-source-id="{$sourceId}">
      <span class="glyphicon glyphicon-plus"></span> Adaugă
    </button>
  </div>
{/if}
