{extends "layout-admin.tpl"}

{block "title"}Surse{/block}

{block "content"}

  <h3>Surse</h3>

  {notice type="info"}
    <div>Duceți cursorul deasupra unui nume de dicționar pentru a vedea mai multe detalii.</div>
    {if $editable}
      <div>
        Trageți de icoana {include "bits/icon.tpl" i=drag_indicator}
        pentru a le reordona rîndurile, apoi apăsați <em>salvează</em>.
      </div>
    {/if}
  {/notice}

  <form method="post">
    {* don't allow both Tablesorter and SortableJS at the same time *}
    <table id="sources" class="table {if !$editable}tablesorter{/if}">
      <thead>
        <tr>
          {if $editable}
            <th>Ordine</th>
          {/if}
          <th class="abbreviation">Nume scurt</th>
          {if $editable}
            <th class="type">Categorie</th>
            <th class="manager">Manager</th>
          {/if}
          <th class="nick">Nume</th>
          <th>% utilizat</th>
          {if $editable}
            <th>Acțiuni</th>
          {/if}
        </tr>
      </thead>
      <tbody {if $editable}class="sortable"{/if}>
        {foreach $src as $s}
          <tr {if $s->id == $highlightSourceId}id="highlightedSource" class="info"{/if}>
            {if $editable}
              <td>
                {include "bits/icon.tpl" i=drag_indicator class="drag-indicator"}
              </td>
            {/if}
            <td class="abbreviation text-nowrap">
              {if $s->link && User::can(User::PRIV_EDIT)}
                <a href="{$s->link}" class="badge badge-muted" target="_blank">
                  {$s->shortName}
                </a>
              {else}
                <span class="badge badge-muted">
                  {$s->shortName}
                </span>
              {/if}
            </td>
            {if $editable}
              <td class="type">
                <span class="sourceType">{if $s->sourceTypeId}{SourceType::getField('name', $s->sourceTypeId)}{else}{/if}</span>
              </td>
              <td class="manager">
                <span class="sourceManager">{if $s->managerId}{User::getField('name', $s->managerId)}{else}{/if}</span>
              </td>
            {/if}
            <td class="nick">
              <input type="hidden" name="ids[]" value="{$s->id}">
              <span class="sourceName">
                {$s->name}
              </span>
              <div class="d-none">
                Autor: {$s->author}<br>
                Editură: {$s->publisher}<br>
                Anul apariției: {$s->year}<br>
                {if $editable}
                  {if $s->importType}
                    <br>
                    Import {$s->getImportTypeLabel()}
                  {/if}
                {/if}
              </div>
            </td>
            <td data-text="{$s->percentComplete}">
              {include "bits/sourcePercentComplete.tpl" s=$s}
            </td>
            {if $editable}
              <td><a href="{Router::link('source/edit')}?id={$s->id}">editează</a></td>
            {/if}
          </tr>
        {/foreach}
      </tbody>
    </table>

    {if $editable}
      <button class="btn btn-primary" type="submit" name="saveButton">
        {include "bits/icon.tpl" i=save}
        <u>s</u>alvează
      </button>

      <a class="btn btn-outline-secondary" href="{Router::link('source/edit')}">
        {include "bits/icon.tpl" i=add}
        adaugă o sursă
      </a>
      <a class="btn btn-link" href="">renunță</a>
    {/if}

  </form>
{/block}
