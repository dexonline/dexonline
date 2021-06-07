{extends "layout-admin.tpl"}

{block "title"}Surse{/block}

{block "content"}

  <h3>Surse</h3>

  <div class="alert alert-info">
    <div>Duceți cursorul deasupra unui nume de dicționar pentru a vedea mai multe detalii</div>
    {if $editable}
      <div>
        Pentru a reordona sursele, apucați de un rând, dar nu de o zonă cu text.
        La sfârșit, nu uitați să salvați.
      </div>
    {/if}
  </div>

  <form method="post">
    <table id="sources" class="table tablesorter">
      <thead>
        <tr>
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
      <tbody>
        {foreach $src as $s}
          <tr {if $s->id == $highlightSourceId}id="highlightedSource" class="info"{/if}>
            <td class="abbreviation text-nowrap">
              {if $s->link && User::can(User::PRIV_EDIT)}
                <a href="{$s->link}" class="badge text-dark bg-light" target="_blank">
                  {$s->shortName}
                </a>
              {else}
                <span class="badge text-dark bg-light">
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

      <a class="btn btn-light" href="{Router::link('source/edit')}">
        {include "bits/icon.tpl" i=add}
        adaugă o sursă
      </a>
      <a class="btn btn-link" href="">renunță</a>
    {/if}

  </form>

  {* Drag-and-drop reordering of rows, only for admins *}
  {if $editable}
    <script>
     jQuery(document).ready(function() {
       $("#sources").tableDnD();
     });
    </script>
  {/if}
{/block}
