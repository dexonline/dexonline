{extends "layout-admin.tpl"}

{block "title"}Lista intrărilor{/block}

{block "content"}
  <h3>Lista intrărilor structurate posibil afectate de înlocuirea în masă</h3>

  <div class="panel-admin">
    <div class="panel panel-default">
      <div class="panel-heading clearfix" id="panel-heading">
        <i class="glyphicon glyphicon-user"></i>
        {$modUser}
      </div>

      <div class="panel-body" id="panel-body">
        {foreach $defResults as $row}
            {$objId=$row->definition->id}

          {include "bits/definition.tpl" showStatus=1 showFlagTypo=1 showUser=0}
          
          <div class="entryWrapper">
            {foreach $entryResults[$objId] as $entry}
              {include "bits/entryLink.tpl" editLink=1 editLinkClass="btn btn-{if $entry[2]==4}primary{elseif $entry[2]==2}warning{else}info{/if} btn-xs"}
            {/foreach}
          </div>
        {/foreach}
      </div>

      <div class="panel-footer">
      </div>
    </div>
  </div>

{/block}
