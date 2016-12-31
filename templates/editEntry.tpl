{extends "layout-admin.tpl"}

{block "title"}
  {if $e->id}
    Intrare {$e->description}
  {else}
    Intrare nouă
  {/if}
{/block}

{block "content"}
  {$renameTrees=$renameTrees|default:false}

  <h3>
    {if $e->id}
      Editează intrarea
    {else}
      Adaugă o intrare
    {/if}
  </h3>

  {include "bits/phpConstants.tpl"}

  <form class="form-horizontal" method="post" role="form">
    <input type="hidden" name="id" value="{$e->id}">

    <div class="row">

      <div class="col-md-6">
        <div class="form-group {if isset($errors.description)}has-error{/if}">
          <label for="description" class="col-md-2 control-label">
            descriere
          </label>
          <div class="col-md-10">
            <div>
              <input type="text"
                     class="form-control"
                     id="description"
                     name="description"
                     value="{$e->description}">
              {include "bits/fieldErrors.tpl" errors=$errors.description|default:null}
            </div>

            <div id="renameDiv"
                 class="checkbox {if !$renameTrees}hidden{/if}">
              <label>
                <input type="checkbox"
                       name="renameTrees"
                       value="1"
                       {if $renameTrees}checked{/if}>
                redenumește și arborii la fel
              </label>
            </div>
          </div>
        </div>

        <div class="form-group {if isset($errors.structStatus)}has-error{/if}">
          <label for="structStatus" class="col-md-2 control-label">
            structurare
          </label>
          <div class="col-md-10">
            {include "bits/structStatusRadio.tpl"
            selected=$e->structStatus
            canEdit=$canEdit.structStatus}
            {include "bits/fieldErrors.tpl" errors=$errors.structStatus|default:null}
          </div>
        </div>

        <div class="form-group">
          <label for="structuristId" class="col-md-2 control-label">
            structurist
          </label>
          <div class="col-md-10">
            <select id="structuristId" name="structuristId" class="form-control">
              <option value="{Entry::STRUCTURIST_ID_NONE}">niciunul</option>
              {foreach $structurists as $s}
                <option value="{$s->id}"
                        {if $s->id == $e->structuristId}selected{/if}>
                  {$s->nick} ({$s->name})
                </option>
              {/foreach}
            </select>
            {include "bits/fieldErrors.tpl" errors=$errors.structuristId|default:null}
          </div>
        </div>
      </div>

      <div class="col-md-6">
        <div class="form-group">
          <label for="lexemIds" class="col-md-2 control-label">
            lexeme
          </label>
          <div class="col-md-10">
            <select id="lexemIds" name="lexemIds[]" style="width: 100%" multiple>
              {foreach $lexemIds as $l}
                <option value="{$l}" selected></option>
              {/foreach}
            </select>
            Tipuri de model:
            {foreach $modelTypes as $mt}
              <span class="label label-default">{$mt}</span>
            {/foreach}
          </div>
        </div>

        <div class="form-group"">
          <label for="treeIds" class="col-md-2 control-label">
            arbori
          </label>
          <div class="col-md-10">
            <select id="treeIds" name="treeIds[]" style="width: 100%" multiple>
              {foreach $treeIds as $t}
                <option value="{$t}" selected></option>
              {/foreach}
            </select>
          </div>
        </div>

        {if $homonyms}
          <div class="form-group">
            <label class="col-md-2">omonime</label>
            <div class="col-md-10">

              {foreach $homonyms as $h}
                <div>
                  {include "bits/entry.tpl" entry=$h editLink=true}
                </div>
              {/foreach}

            </div>
          </div>
        {/if}
      </div>

    </div>

    <button type="submit" class="btn btn-success" name="saveButton">
      <i class="glyphicon glyphicon-floppy-disk"></i>
      <u>s</u>alvează
    </button>

    <button type="button" class="btn btn-default" data-toggle="modal" data-target="#mergeModal">
      <i class="glyphicon glyphicon-resize-small"></i>
      unifică cu...
    </button>

    <button type="button" class="btn btn-default" data-toggle="modal" data-target="#cloneModal">
      <i class="glyphicon glyphicon-duplicate"></i>
      clonează...
    </button>

    <button type="submit" class="btn btn-default" name="createTree">
      <i class="glyphicon glyphicon-tree-deciduous"></i>
      creează un arbore de sensuri
    </button>

    {if $e->id}
      <a href="http://wiki.dexonline.ro/wiki/Intrare:{$e->id}"
         class="btn btn-default"
         target="_blank">
        <i class="glyphicon glyphicon-comment"></i>
        wiki
      </a>
    {/if}

    <a class="btn btn-link" href="{if $e->id}?id={$e->id}{/if}">
      renunță
    </a>

    <div class="pull-right">
      <button type="submit" class="btn btn-danger" name="delete">
        <i class="glyphicon glyphicon-trash"></i>
        șterge
      </button>

      <button type="submit"
              class="btn btn-danger"
              name="deleteExt"
              title="șterge intrarea, lexemele de tip T și arborii goi">
        <i class="glyphicon glyphicon-trash"></i>
        <i class="glyphicon glyphicon-trash"></i>
        șterge extins
      </button>
    </div>
  </form>

  <div class="modal fade" id="mergeModal" role="dialog">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <form method="post" role="form">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal">
              <span aria-hidden="true">&times;</span>
            </button>
            <h4 class="modal-title">Unifică intrarea cu...</h4>
          </div>

          <div class="modal-body">
            <input type="hidden" name="id" value="{$e->id}">
            <select id="mergeEntryId" name="mergeEntryId" class="form-control">
            </select>
          </div>

          <div class="modal-footer">
            <button type="submit" class="btn btn-primary" name="mergeButton">
              <i class="glyphicon glyphicon-resize-small"></i>
              unifică
            </button>
            <button type="button" class="btn btn-link" data-dismiss="modal">renunță</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <div class="modal fade" id="cloneModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <form method="post" role="form">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal">
              <span aria-hidden="true">&times;</span>
            </button>
            <h4 class="modal-title">Clonează intrarea</h4>
          </div>

          <div class="modal-body">
            <input type="hidden" name="id" value="{$e->id}">
            <div class="checkbox">
              <label>
                <input type="checkbox" name="cloneDefinitions" checked>
                copiază asocierile cu definiții
              </label>
            </div>
            <div class="checkbox">
              <label>
                <input type="checkbox" name="cloneLexems" checked>
                copiază asocierile cu lexeme
              </label>
            </div>
            <div class="checkbox">
              <label>
                <input type="checkbox" name="cloneTrees" checked>
                copiază asocierile cu arbori
              </label>
            </div>
          </div>

          <div class="modal-footer">
            <button type="submit" class="btn btn-primary" name="cloneButton">
              <i class="glyphicon glyphicon-duplicate"></i>
              clonează
            </button>
            <button type="button" class="btn btn-link" data-dismiss="modal">renunță</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  {if $e->id}
    <h3>Arbori de sensuri asociați ({$e->getTrees()|count})</h3>

    <form>
      <div class="form-group form-inline">
        <select id="treeFilterSelect" class="form-control">
          <option value="{Tree::ST_VISIBLE}">doar arborii vizibili</option>
          <option value="{Tree::ST_HIDDEN}">doar arborii ascunși</option>
          <option value="-1">toți arborii</option>
        </select>
      </div>
    </form>

    {foreach $e->getTrees() as $t}
      <div class="panel panel-default tree tree-status-{$t->status}">
        <div class="panel-heading">
          {$t->description}
          <a href="editTree.php?id={$t->id}" class="pull-right">
            <i class="glyphicon glyphicon-pencil"></i>
            editează
          </a>
        </div>
        <div class="panel-body">
          {include "bits/editableMeaningTree.tpl"
          meanings=$t->getMeanings()
          id="meaningTree-{$t->id}"}
        </div>
      </div>
    {/foreach}

    <h3>Definiții asociate ({$searchResults|count})</h3>

    <form class="form-inline">
      <div class="form-group">

        <select id="defFilterSelect" class="form-control">
          <option value="">toate</option>
          <option value="structured">structurate</option>
          <option value="unstructured">nestructurate</option>
        </select>

        <select class="toggleRepSelect form-control" data-order="1">
          <option value="0">text</option>
          <option value="1" selected>html</option>
        </select>

        <select class="toggleRepSelect form-control" data-order="2">
          <option value="0">expandat</option>
          <option value="1" selected>abreviat</option>
        </select>

      </div>
    </form>

    {foreach $searchResults as $row}
      {$def=$row->definition}
      <div class="defWrapper {if $def->structured}structured{else}unstructured{/if}" id="def_{$def->id}">
        <div data-code="0" class="rep internal hiddenRep">{$def->internalRepAbbrev|escape}</div>
        <div data-code="1" class="rep hiddenRep">{$def->htmlRepAbbrev}</div>
        <div data-code="2" class="rep internal hiddenRep">{$def->internalRep|escape}</div>
        <div data-code="3" data-active class="rep">{$def->htmlRep}</div>
        <p class="defDetails text-muted">
          id: {$def->id}
          | sursa: {$row->source->shortName|escape}
          | starea: {$def->getStatusName()}
          | <a href="{$wwwRoot}admin/definitionEdit.php?definitionId={$def->id}">editează</a>
          | <a href="?id={$e->id}&amp;dissociateDefinitionId={$def->id}"
               class="dissociateLink"
               title="disociază definiția de intrare"
               >disociază</a>
          | <a href="#" class="toggleRepLink" title="comută între notația internă și HTML"
               data-value="1" data-order="1" data-other-text="html">text</a>
          | <a href="#" class="toggleRepLink" title="contractează sau expandează abrevierile"
               data-value="1" data-order="2" data-other-text="abreviat">expandat</a>

          |
          <a href="#"
             title="comută definiția între structurată și nestructurată"
             >
            <span class="toggleStructuredLink" {if !$def->structured}style="display: none"{/if}>
              <i class="glyphicon glyphicon-ok"></i> structurată
            </span>
            <span class="toggleStructuredLink" {if $def->structured}style="display: none"{/if}>
              <i class="glyphicon glyphicon-remove"></i> nestructurată
            </span>
          </a>
        </p>

        {if $row->comment}
          <div class="commentInternalRep">
            Comentariu: {$row->comment->contents} -
            <a href="{$wwwRoot}utilizator/{$row->commentAuthor->nick|escape:"url"}">{$row->commentAuthor->nick|escape}</a>
          </div>
        {/if}
      </div>
    {/foreach}
  {/if}
{/block}
