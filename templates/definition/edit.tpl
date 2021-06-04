{extends "layout-admin.tpl"}

{block "title"}Editare definiție{/block}

{block "content"}
  {if $isOcr}
    {$title='Adăugare definiție OCR'}
  {else if $def->id}
    {$title="Editare definiție {$def->id}"}
  {else}
    {$title="Trimite o definiție nouă"}
  {/if}
  <h3>
    {$title}
    <span class="float-end">
      <a class="btn btn-link"
        href="https://wiki.dexonline.ro/wiki/Editarea_defini%C8%9Biilor"
        target="_blank">
        {include "bits/icon.tpl" i=info}
        ajutor
      </a>
    </span>
  </h3>

  <form method="post" class="form-horizontal">
    <input type="hidden" name="definitionId" value="{$def->id}">
    <input type="hidden" name="isOcr" value="{$isOcr}">

    <div class="row">
      <div class="col-md-6">
        <div class="row mb-3">
          <label class="col-sm-2 col-md-4 col-form-label">sursă</label>
          <div class="col-sm-10 col-md-8">
            {if $source->canModerate || !$def->id}
              {include "bits/sourceDropDown.tpl"
                sources=$allModeratorSources
                sourceId=$def->sourceId
                skipAnySource=true}
            {else}
              <input type="hidden" name="source" value="{$def->sourceId}">
              <input class="form-control" type="text" disabled value="{$source->shortName}">
            {/if}
          </div>
        </div>

        <div class="row mb-3">
          <label class="col-sm-2 col-md-4 col-form-label">stare</label>
          <div class="col-sm-10 col-md-8">
            {include "bits/statusDropDown.tpl"
              name="status"
              selectedStatus=$def->status
              disabled=!$canEditStatus}
          </div>
        </div>

        <div class="row mb-3">
          <label class="col-sm-2 col-md-4 col-form-label">volum</label>
          <div class="col-sm-4 col-md-3">
            <input
              class="form-control"
              type="number"
              name="volume"
              value="{$def->volume}">
          </div>
          <div class="col-sm-2 col-md-2">
            <label class="col-form-label">pagină</label>
          </div>
          <div class="col-sm-4 col-md-3">
            <input
              class="form-control"
              type="number"
              name="page"
              value="{$def->page}">
          </div>
        </div>
      </div>

      <div class="col-md-6">

        <div class="row mb-3">
          <label class="col-sm-2 col-form-label">etichete</label>
          <div class="col-sm-10">
            <select id="tagIds" name="tagIds[]" class="form-control select2Tags" multiple>
              {foreach $tagIds as $t}
                <option value="{$t}" selected></option>
              {/foreach}
            </select>

            {include "bits/frequentObjects.tpl"
              name="definitionTags"
              type="tags"
              target="#tagIds"}
          </div>
        </div>

        <div class="row mb-3">
          <label for="entryIds" class="col-sm-2 col-form-label">intrări</label>
          <div class="col-sm-10 overflownSelect2">
            <select id="entryIds" name="entryIds[]" style="width: 100%" multiple>
              {foreach $entryIds as $e}
                <option value="{$e}" selected></option>
              {/foreach}
            </select>

            <div class="float-end">
              <button
                id="refreshEntriesButton"
                type="button"
                class="btn btn-light btn-sm"
                title="recalculează lista de intrări pe baza cuvîntului definit">
                {include "bits/icon.tpl" i=refresh}
              </button>
              <button
                id="clearEntriesButton"
                type="button"
                class="btn btn-light btn-sm"
                title="golește lista de intrări">
                {include "bits/icon.tpl" i=delete}
              </button>
            </div>
          </div>
        </div>

      </div>
    </div>

    {if count($typos)}
      <div class="row mb-3">
        <label class="col-sm-2 col-form-label">greșeli de tipar</label>
        <div class="col-sm-10">
          {foreach $typos as $typo}
            <p id="typo{$typo->id}" class="text-warning typo-wrapper">
              {$typo->problem|escape}
              <span class="text-muted">
                [{$typo->userName}]
              </span>
              <a href="#"
                class="ignore-typo"
                data-typo-id="{$typo->id}">
                ignoră
              </a>
            </p>
          {/foreach}
        </div>
      </div>
    {/if}

    <div class="row mb-1">
      <label class="col-sm-2 col-form-label">conținut</label>
      <div class="col-sm-10">
        <textarea
          id="internalRep"
          name="internalRep"
          class="form-control tinymceTextarea"
          rows="10"
          autofocus
        >{$def->internalRep|escape}</textarea>
      </div>
    </div>

    <div class="row mb-3">
      <div class="col-sm-10 offset-sm-2">
        {include "bs/checkbox.tpl"
          name=structured
          label='Definiția a fost structurată'
          checked=$def->structured}
      </div>
    </div>

    <div class="row mb-3">
      <div class="col-sm-10 offset-sm-2 d-flex justify-content-between">

        <span>
          <button id="refreshButton"
            type="button"
            name="refreshButton"
            class="btn btn-light">
            {include "bits/icon.tpl" i=refresh}
            <u>r</u>eafișează
          </button>

          <button
            type="submit"
            name="saveButton"
            class="btn btn-primary"
            {if !$canEdit}
            disabled
            title="stagiarii nu pot modifica definiții introduse de altcineva"
            {/if}>
            {include "bits/icon.tpl" i=save}
            <u>s</u>alvează
          </button>

          {if $isOcr}
            <button
              type="submit"
              class="btn btn-primary"
              name="but_next_ocr"
              {if !$canEdit}
              disabled
              title="stagiarii nu pot modifica definiții introduse de altcineva"
              {/if}>
              salvează și preia următoarea definiție OCR
            </button>
          {/if}

          {if $def->id}
            <a id="wikiLink"
              href="https://wiki.dexonline.ro/wiki/Definiție:{$def->id}?description={$def->lexicon|escape}"
              class="btn btn-light"
              title="creează o pagină wiki pentru această definiție"
              target="_blank">
              {include "bits/icon.tpl" i=comment}
              wiki
            </a>
          {/if}
        </span>

        <span>
          {include "bits/definitionMenuProper.tpl"
            ulClass="btn btn-link mb-0"
            showEditLink=false
            showHistory=true
            showId=false
            showPageModal=false
            showSource=false
            showUser=false}

          <button id="tinymceToggleButton"
            type="button"
            class="btn btn-light doubleText"
            data-other-text="ascunde TinyMCE"
            title="TinyMCE este un editor vizual (cu butoane de bold, italic etc.).">
            arată TinyMCE
          </button>

        </span>
      </div>
    </div>

  </form>

  <div class="card mb-3">
    <div class="card-header">
      Previzualizare
    </div>

    <div class="card-body pb-0">
      <p class="def" id="defPreview">{HtmlConverter::convert($def)}</p>
    </div>
  </div>

  <div id="footnotes">
    {include "bits/footnotes.tpl" footnotes=$def->getFootnotes()}
  </div>

  <pre id="similarRecord">{$sim->getJson()|escape}</pre>

  <div id="similarSourceMessageYes">
    <div class="card mb-3">

      <div class="card-header">
        Definiția corespunzătoare din <span class="similarSourceName"></span>
        <a
          class="float-end"
          id="similarDefinitionEdit"
          href="{$sim->definition->id|default:''}"
          target="_blank">
          {include "bits/icon.tpl" i=edit}
          editează
        </a>
      </div>

      <div class="card-body">
        <div id="similarRep"></div>
      </div>
    </div>

    <div class="card mb-3">

      <div class="card-header" id="similarNotIdentical">
        {include "bits/icon.tpl" i=clear class="text-danger"}
        Diferențe față de definiția din <span class="similarSourceName"></span>:
      </div>

      <div class="card-header" id="similarIdentical">
        {include "bits/icon.tpl" i=done class="text-success"}
        Definiția este identică cu cea din <span class="similarSourceName"></span>.
      </div>

      <div class="card-body" id="similarDiff"></div>
    </div>
  </div>

  <div id="similarSourceMessageNoSource">
    Nu există o sursă anterioară.
  </div>

  <div id="similarSourceMessageNoDefinition">
    Nu există o definiție similară în <span class="similarSourceName"></span>.
  </div>

  <div id="diffPopover">
    <button type="button" class="btn btn-light btn-sm diffButton" data-insert="1">
      {include "bits/icon.tpl" i=add}
      inserează textul și în <span class="similarSourceName"></span>
    </button>
    <button type="button" class="btn btn-light btn-sm diffButton" data-insert="0">
      {include "bits/icon.tpl" i=remove}
      șterge textul din <span class="similarSourceName"></span>
    </button>
    <a href="#" class="btn btn-link btn-sm diffCancel">anulează</a>
  </div>

  {include "bits/pageModal.tpl"}
{/block}
