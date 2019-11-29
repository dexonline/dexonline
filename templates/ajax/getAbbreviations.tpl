<div class="panel-admin">
  <div class="panel panel-default">
    <div class="panel-heading" id="panel-heading">
      Lista abrevierilor
    </div>
    {if $results|count  != 0}
      <table id="table-abbrevs" class="table table-condensed table-striped table-bordered">
        <thead>
          <tr>
            <th data-column-id="id" data-type="numeric" data-identifier="true" data-sorter="false">Id</th>
            <th data-column-id="enforced" data-sorter="false">Imp.</th>
            <th data-column-id="ambiguous" data-sorter="false">Amb.</th>
            <th data-column-id="caseSensitive" data-sorter="false">CS</th>
            <th data-column-id="short" style="width:20%">Abreviere</th>
            <th data-column-id="internalRep" class="internalRep"></th>
            <th data-column-id="html" style="width:50%">Detalierea abrevierii</th>
            {if User::can(User::PRIV_ADMIN)}
              <th data-column-id="commands" data-formatter="commands" data-sorter="false">Comenzi</th>
            {/if}
          </tr>
        </thead>
        <tbody>
          {foreach $results as $row}
            {include "bits/abbrevRow.tpl"}
          {/foreach}
        </tbody>
        {include "bits/pager.tpl" id="abbrevsPager" colspan="7"}
      </table>
    {else}
      <p class="panel-body text-danger">
        Nu există abrevieri încărcate pentu dicționarul ales.
      </p>
    {/if}
    {*<div class="panel-footer text-center clearfix">
      <span class="label label-default">Total abrevieri: <span id="abbrevCount">{$results|count}</span></span>
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
