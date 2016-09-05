{extends "layout-admin.tpl"}

{block name=title}Editare lexem: {$lexem->form}{/block}

{block name=content}
  <h3>
    Editare lexem: {$lexem->form}
    <span class="pull-right">
      <small>
        <a href="http://wiki.dexonline.ro/wiki/Editarea_lexemelor">
          <i class="glyphicon glyphicon-question-sign"></i>
          instrucțiuni
        </a>
      </small>
    </span>
  </h3>

  <script>
   canEdit = { 'paradigm': {$canEdit.paradigm}, 'loc': {$canEdit.loc} };
  </script>

  <form action="lexemEdit.php" method="post">
    {include file="admin/lexemEditActions.tpl"}

    <div class="panel panel-default">

      <div class="panel-heading">Proprietăți</div>

      <div class="panel-body">
        <input type="hidden" name="lexemId" value="{$lexem->id}">

        <div class="row">
          <div class="col-md-6 form-horizontal">

            <div class="form-group">
              <label for="lexemForm" class="col-md-2 control-label">formă</label>
              <div class="col-md-10">
                <input type="text"
                       class="form-control"
                       id="lexemForm"
                       name="lexemForm"
                       value="{$lexem->form|escape}"
                       {if !$canEdit.form}readonly{/if}>

                <div class="checkbox">
                  <label>
                    <input type="checkbox" name="needsAccent" value="1" {if !$lexem->noAccent}checked{/if}>
                    necesită accent
                  </label>
                </div>
              </div>
            </div>

            {include "bits/fhf.tpl"
            field="lexemDescription"
            value=$lexem->description
            label="descriere"
            placeholder="opțională, pentru diferențierea omonimelor"
            readonly=!$canEdit.description}

            {include "bits/fhf.tpl"
            field="lexemNumber"
            type="number"
            value=$lexem->number
            label="număr"
            placeholder="opțional, pentru numerotarea omonimelor"
            readonly=!$canEdit.general}
            
            {if $homonyms}
              <div class="form-group">
                <label class="col-md-2">omonime</label>
                <div class="col-md-10">

                  {foreach from=$homonyms item=h}
                    <div>
                      {include file="bits/lexemLink.tpl" lexem=$h}
                      {$h->modelType}{$h->modelNumber}{$h->restriction}
                    </div>
                  {/foreach}

                </div>
              </div>
            {/if}

            <div class="form-group">
              <label for="entryId" class="col-md-2 control-label">intrare</label>
              <div class="col-md-10">
                <select id="entryId" name="entryId" class="form-control">
                  {if $lexem->entryId}
                    <option value="{$lexem->entryId}" selected></option>
                  {/if}
                </select>
              </div>
            </div>
            
          </div>

          <div class="col-md-6 form-horizontal">

            {include "bits/fhf.tpl"
            field="hyphenations"
            value=$lexem->hyphenations
            label="silabisiri"
            placeholder="opționale, despărțite prin virgule"
            readonly=!$canEdit.hyphenations}

            {include "bits/fhf.tpl"
            field="pronunciations"
            value=$lexem->pronunciations
            label="pronunții"
            placeholder="opționale, despărțite prin virgule"
            readonly=!$canEdit.pronunciations}

            <div class="form-group">
              <label for="tagIds" class="col-md-2 control-label">etichete</label>
              <div class="col-md-10">
                <select id="tagIds" name="tagIds[]" class="form-control" multiple>
                  {foreach $tagIds as $t}
                    <option value="{$t}" selected></option>
                  {/foreach}
                </select>

                <div class="checkbox">
                  <label>
                    <input type="checkbox" name="main" value="1" {if $lexem->main}checked{/if}>
                    formă principală
                  </label>
                </div>

                <div class="checkbox">
                  <label>
                    <input type="checkbox"
                           name="stopWord"
                           value="1"
                           {if $lexem->stopWord}checked{/if}
                           {if !$canEdit.stopWord}disabled{/if}
                           >
                    ignoră la căutările full-text
                  </label>
                </div>
              </div>
            </div>

          </div>
        </div>
      </div>
    </div>

    <div class="panel panel-default">

      <div class="panel-heading">Model de flexiune</div>

      <div class="panel-body">

        <div class="row">
          <div class="col-md-6 form-horizontal">

            {assign var="readonly" value=!$canEdit.loc && $lexem->isLoc}

            <div class="form-group">
              <label class="col-md-3 control-label">tip + număr</label>

              <div class="col-md-9 form-inline" data-model-dropdown>
                <input type="hidden" name="locVersion" value="6.0" data-loc-version>

                <select name="modelType" class="form-control" {if $readonly}disabled{/if} data-model-type data-selected="{$lexem->modelType}">
                </select>

                <select name="modelNumber" class="form-control" {if $readonly}disabled{/if} data-model-number data-selected="{$lexem->modelNumber}">
                </select>
                
                <input type="text"
                       class="form-control"
                       name="restriction"
                       value="{$lexem->restriction}"
                       size="5"
                       placeholder="restricții"
                       {if $readonly}readonly{/if}>
              </div>

            </div>

            {if !$readonly}
              <div class="form-group">
                <div class="col-md-offset-3 col-md-9">
                  <select class="similarLexem"></select>
                </div>
              </div>
            {/if}

            <div class="form-group">
              <div class="col-md-offset-3 col-md-9">
                <div class="checkbox">
                  <label>
                    <input type="checkbox"
                           name="isLoc"
                           value="1"
                           {if $lexem->isLoc}checked{/if}
                           {if !$canEdit.loc}disabled{/if}
                           >
                    inclus în LOC
                  </label>
                </div>
              </div>
            </div>

            <div class="form-group">
              <label class="col-md-3 control-label">surse</label>
              <div class="col-md-9">
                <select id="sourceIds"
                        class="form-control""
                        name="sourceIds[]"
                        multiple
                        {if !$canEdit.sources}disabled{/if}>
                  {foreach $lexem->getSourceIds() as $lsId}
                    <option value="{$lsId}" selected></option>
                  {/foreach}
                </select>
              </div>
            </div>
          </div>

          <div class="col-md-6 form-horizontal">
            {include "bits/fhf.tpl"
            field="notes"
            value=$lexem->notes
            label="precizări"
            placeholder="explicații despre sursa flexiunii"
            readonly=!$canEdit.tags}

            <div class="form-group">
              <label class="col-md-2">comentariu</label>

              <div class="col-md-10">
                <textarea name="lexemComment" class="form-control" rows="4"
                          placeholder="Comentarii și/sau greșeli observate în paradigmă"
                          >{$lexem->comment|escape}</textarea>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="panel panel-default">
      <div class="panel-heading">Paradigmă</div>
      <div class="panel-body">
        {include "paradigm/paradigm.tpl" lexem=$lexem}
      </div>
    </div>

    <div class="panel panel-default">
      <div class="panel-heading">Definitiții ({$searchResults|count})</div>
      <div class="panel-body panel-admin">
        {foreach from=$searchResults item=row}
          {$def=$row->definition}
          <div class="defWrapper">
            <p class="def">{$row->definition->htmlRep}</p>

            <p class="defDetails text-muted">
              <small>
                id: {$def->id}
                | sursa: {$row->source->shortName|escape}
                | starea: {$def->getStatusName()}
                | <a href="{$wwwRoot}admin/definitionEdit.php?definitionId={$def->id}">editează</a>
              </small>
            </p>

            {if $row->comment}
              <div class="panel panel-default panel-comment">
                <div class="panel-body">
                  <i class="glyphicon glyphicon-comment"></i>
                  {$row->comment->htmlContents} -
                  <b>{$row->commentAuthor->nick|escape}</b>
                </div>
              </div>
            {/if}
          </div>
        {/foreach}
      </div>
    </div>

  </form>
{/block}
