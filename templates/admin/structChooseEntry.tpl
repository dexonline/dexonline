{extends "layout-admin.tpl"}

{block name=title}Intrări ușor de structurat{/block}

{block name=content}
  <h3> {$entries|count} de intrări ușor de structurat</h3>

  {foreach $entries as $i => $e}
    <div class="panel panel-default">

      <div class="panel-heading">
        {include "bits/entryLink.tpl" entry=$e}
      </div>

      <div class="panel-body">
        {foreach $searchResults[$i] as $row}
          <p>
            {$row->definition->htmlRep}
            <small class="text-muted">{$row->source->shortName}</small>
          </p>
        {/foreach}
      </div>

    </div>
  {/foreach}

{/block}
