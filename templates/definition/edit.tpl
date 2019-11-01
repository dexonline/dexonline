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
    <span class="pull-right">
      <a class="btn btn-link"
        href="https://wiki.dexonline.ro/wiki/Editarea_defini%C8%9Biilor"
        target="_blank">
        <i class="glyphicon glyphicon-question-sign"></i>
        ajutor
      </a>
    </span>
  </h3>

  <form method="post" class="form-horizontal">
    <input type="hidden" name="definitionId" value="{$def->id}">
    <input type="hidden" name="isOcr" value="{$isOcr}">

    <div class="row">
      <div class="col-md-6">
        <div class="form-group">
          <label class="col-sm-2 col-md-4 control-label">sursă</label>
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

        <div class="form-group">
          <label class="col-sm-2 col-md-4 control-label">stare</label>
          <div class="col-sm-10 col-md-8">
            {include "bits/statusDropDown.tpl"
              name="status"
              selectedStatus=$def->status
              disabled=!$canEditStatus}
          </div>
        </div>

        <div class="form-group">
          <label class="col-sm-2 col-md-4 control-label">volum</label>
          <div class="col-sm-2 col-md-2">
            <input
              class="form-control"
              type="number"
              name="volume"
              value="{$def->volume}">
          </div>
          <label class="col-sm-2 col-md-2 control-label">pagină</label>
          <div class="col-sm-3 col-md-3">
            <input
              class="form-control"
              type="number"
              name="page"
              value="{$def->page}">
          </div>
        </div>
      </div>

      <div class="col-md-6">

        <div class="form-group">
          <label class="col-sm-2 control-label">etichete</label>
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

        <div class="form-group">
          <label for="entryIds" class="col-sm-2 control-label">intrări</label>
          <div class="col-sm-10 overflownSelect2">
            <select id="entryIds" name="entryIds[]" style="width: 100%" multiple>
              {foreach $entryIds as $e}
                <option value="{$e}" selected></option>
              {/foreach}
            </select>

            <div class="clearfix">
              <div class="pull-right voffset">
                <button
                  id="refreshEntriesButton"
                  type="button"
                  class="btn btn-default btn-xs"
                  title="recalculează lista de intrări pe baza cuvîntului definit">
                  <i class="glyphicon glyphicon-refresh"></i>
                </button>
                <button
                  id="clearEntriesButton"
                  type="button"
                  class="btn btn-default btn-xs"
                  title="golește lista de intrări">
                  <i class="glyphicon glyphicon-trash"></i>
                </button>
              </div>
            </div>
          </div>
        </div>

      </div>
    </div>

    {if count($typos)}
      <div class="form-group">
        <label class="col-sm-2 control-label">greșeli de tipar</label>
        <div class="col-sm-10">
          {foreach $typos as $typo}
            <p class="bg-danger voffset1">{$typo->problem|escape}</p>
          {/foreach}
        </div>
      </div>
    {/if}

    <div class="form-group">
      <label class="col-sm-2 control-label">conținut</label>
      <div class="col-sm-10">
        <textarea
          id="internalRep"
          name="internalRep"
          class="form-control tinymceTextarea"
          rows="10"
          autofocus
        >{$def->internalRep|escape}</textarea>

        {** These aren't logically connected, but we like them vertically compressed **}
        <div class="checkbox">
          <label>
            <input type="checkbox" name="structured" value="1" {if $def->structured}checked{/if}>
            Definiția a fost structurată
          </label>
        </div>
      </div>
    </div>

    <div class="form-group">
      <label class="col-sm-2 control-label"></label>
      <div class="col-sm-10">

        <button id="refreshButton"
          type="button"
          name="refreshButton"
          class="btn btn-primary">
          <i class="glyphicon glyphicon-refresh"></i>
          <u>r</u>eafișează
        </button>

        <button
          type="submit"
          name="saveButton"
          class="btn btn-success"
          {if !$canEdit}
          disabled
          title="stagiarii nu pot modifica definiții introduse de altcineva"
          {/if}>
          <i class="glyphicon glyphicon-floppy-disk"></i>
          <u>s</u>alvează
        </button>

        {if $isOcr}
          <button
            type="submit"
            class="btn btn-success"
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
            class="btn btn-default"
            title="creează o pagină wiki pentru această definiție"
            target="_blank">
            <i class="glyphicon glyphicon-comment"></i>
            wiki
          </a>
        {/if}

        <div class="btn-group pull-right" id="tinymceButtonWrapper">
          <span class="btn btn-link">
            {include "bits/definitionMenuProper.tpl"
              showEditLink=false
              showHistory=true
              showId=false
              showPageModal=false
              showSource=false
              showUser=false
            }
          </span>

          <button id="tinymceToggleButton"
            type="button"
            class="btn btn-default doubleText"
            data-other-text="ascunde TinyMCE"
            href="#"
            title="TinyMCE este un editor vizual (cu butoane de bold, italic etc.).">
            arată TinyMCE
          </button>
        </div>
      </div>
    </div>

  </form>

  <div class="panel panel-default">
    <div class="panel-heading">
      Previzualizare
    </div>

    <div class="panel-body">
      <p class="def" id="defPreview">{HtmlConverter::convert($def)}</p>
    </div>
  </div>

  <div id="footnotes">
    {include "bits/footnotes.tpl" footnotes=$def->getFootnotes()}
  </div>

  <pre id="similarRecord">{$sim->getJson()|escape}</pre>

  <div id="similarSourceMessageYes">
    <div class="panel panel-default">

      <div class="panel-heading">
        Definiția corespunzătoare din <span class="similarSourceName"></span>
        <a
          class="pull-right"
          id="similarDefinitionEdit"
          href="{$sim->definition->id|default:''}"
          target="_blank">
          <i class="glyphicon glyphicon-pencil"></i>
          editează
        </a>
      </div>

      <div class="panel-body">
        <div id="similarRep"></div>
      </div>
    </div>

    <div class="panel panel-default">

      <div class="panel-heading" id="similarNotIdentical">
        <i class="glyphicon glyphicon-remove text-danger"></i>
        Diferențe față de definiția din <span class="similarSourceName"></span>:
      </div>

      <div class="panel-heading" id="similarIdentical">
        <i class="glyphicon glyphicon-ok text-success"></i>
        Definiția este identică cu cea din <span class="similarSourceName"></span>.
      </div>

      <div class="panel-body" id="similarDiff"></div>
    </div>
  </div>

  <div id="similarSourceMessageNoSource">
    Nu există o sursă anterioară.
  </div>

  <div id="similarSourceMessageNoDefinition">
    Nu există o definiție similară în <span class="similarSourceName"></span>.
  </div>

  <div id="diffPopover">
    <button type="button" class="btn btn-default btn-sm diffButton" data-insert="1">
      <i class="glyphicon glyphicon-plus"></i>
      inserează textul și în <span class="similarSourceName"></span>
    </button>
    <button type="button" class="btn btn-default btn-sm diffButton" data-insert="0">
      <i class="glyphicon glyphicon-minus"></i>
      șterge textul din <span class="similarSourceName"></span>
    </button>
    <a href="#" class="btn btn-link btm-sm diffCancel">anulează</a>
  </div>

  {include "bits/pageModal.tpl"}
{/block}
