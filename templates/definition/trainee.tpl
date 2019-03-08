{extends "layout-admin.tpl"}

{block "title"}Definițiile mele{/block}

{block "content"}
  <h3>Definițiile mele ({$defs|count})</h3>

  <table id="defsTable" class="table">
    <thead>
      <tr>
        <th>cuvânt-titlu</th>
        <th>sursă</th>
        <th>stare</th>
        <th data-sortInitialOrder="desc">dată</th>
      </tr>
    </thead>

    {include "bits/pager.tpl" id="defsPager" colspan="4"}

    <tbody>
      {foreach $defs as $d}
        <tr>
          <td>
            <a href="{Router::link('definition/edit')}?definitionId={$d->id}">
              {$d->lexicon|default:'[nedefinit]'}
            </a>
          </td>
          <td>{$sourceMap[$d->sourceId]->shortName}</td>
          <td>{$d->getStatusName()}</td>
          <td data-text="{$d->createDate}">
            {$d->createDate|date_format:"%d.%m.%Y"}
          </td>
        </tr>
      {/foreach}
    </tbody>
  </table>
{/block}
