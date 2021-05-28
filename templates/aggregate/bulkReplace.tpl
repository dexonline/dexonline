{extends "layout-admin.tpl"}

{block "title"}Înlocuire în masă{/block}

{block "content"}
  <h3>Înlocuire în masă</h3>

  <div class="card">
    <div class="card-header d-flex">
      <span class="flex-grow-1">
        {include "bits/icon.tpl" i=person}
        {$modUser}
      </span>
      {if $target == 1}
        {* display the toggleAll checkboxes only for definitions *}
        <div class="form-check">
          <label class="form-check-label text-primary">
            <input
              class="form-check-input toggleAll"
              data-type="structured"
              type="checkbox"
              checked>
            structurate
          </label>
        </div>

        <div class="form-check ms-2">
          <label class="form-check-label">
            <input
              class="form-check-input toggleAll"
              data-type="unstructured"
              type="checkbox"
              checked>
            nestructurate
          </label>
        </div>
      {/if}
    </div>

    <div class="card-body" id="card-body">
      {foreach $objects as $row}
        {if $target == 1}
          {$objId=$row->definition->id}
        {else}
          {$objId=$row->id}
        {/if}

        {if $target == 1}
          {* display the checkbox only for definitions *}
          <div class="form-check float-end ms-3">
            <input
              class="form-check-input objCheckbox"
              data-type="{if $row->definition->structured}structured{else}unstructured{/if}"
              type="checkbox"
              value="{$objId}"
              checked>
          </div>
        {/if}

        {if $target == 1}
          {include "bits/definition.tpl" showStatus=1 showFlagTypo=1 showUser=0 showStructuredWrapper=0}
        {else}
          {include "bits/meaning.tpl" m=$row}
        {/if}
      {/foreach}
    </div>

    <div class="card-footer d-flex justify-content-between">
      <span>
        {include "bits/icon.tpl" i=filter_alt}
        <div class="form-check form-check-inline">
          <label class="form-check-label">
            <input class="form-check-input" type="radio" name="radiodiff" checked>Toate diferențele
          </label>
        </div>
        <div class="form-check form-check-inline">
          <label class="form-check-label">
            <input class="form-check-input" type="radio" name="radiodiff" value="del">Doar ștergerile
          </label>
        </div>
        <div class="form-check form-check-inline">
          <label class="form-check-label">
            <input class="form-check-input" type="radio" name="radiodiff" value="ins">Doar inserările
          </label>
        </div>
      </span>
      <div>
        <span id="chng">{$objects|count}</span>
        <span id="de">{$de}</span>
        {$targetName} vor fi modificate din {$remaining} rămase
      </div>
    </div>
  </div>

  <form method="post" class="mt-3 d-flex">
    <input type="hidden" name="search" value="{$search|escape}">
    <input type="hidden" name="replace" value="{$replace|escape}">
    <input type="hidden" name="target" value="{$target}">
    <input type="hidden" name="sourceId" value="{$sourceId}">
    <input type="hidden" name="limit" value="{$limit}">
    <input type="hidden" name="excludedIds" value="">
    <input type="hidden" name="structuredChanged" value="{$structuredChanged}">

    <span class="flex-grow-1">
      <button type="submit" class="btn btn-primary" name="saveButton">
        {include "bits/icon.tpl" i=save}
        <u>s</u>alvează {if $objects|count != $remaining} și încarcă următoarele {/if}
      </button>
      <a href="{Router::link('aggregate/dashboard')}" class="btn btn-link">
        {include "bits/icon.tpl" i=arrow_back}
        înapoi la pagina moderatorului
      </a>
    </span>
    {if $structuredChanged}
      <a
        href="{Router::link('aggregate/bulkReplaceStructured')}"
        class="btn btn-light"
        target="_blank">
        definiții structurate modificate
      </a>
    {/if}
  </form>
{/block}
