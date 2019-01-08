{extends "layout-admin.tpl"}

{block "title"}Intrări ușor de structurat{/block}

{block "content"}
  <h3> {$entries|count} de intrări ușor de structurat</h3>

  {foreach $entries as $i => $e}
    <div class="panel panel-default">

      <div class="panel-heading">
        {include "bits/entry.tpl" entry=$e editLink=true}
      </div>

      <div class="panel-body">
        {foreach $searchResults[$i] as $row}
          <p>
            {HtmlConverter::convert($row->definition)}
            <small class="text-muted">{$row->sources[0]->shortName}</small>
          </p>
        {/foreach}
      </div>

    </div>
  {/foreach}

{/block}
