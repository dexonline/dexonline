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

  <div id="isStructurist" class="hidden">{User::can(User::PRIV_STRUCT)}</div>

  <h3>
    {if $e->id}
      Editează intrarea
    {else}
      Adaugă o intrare
    {/if}
  </h3>

  {include "bits/phpConstants.tpl"}

  <form class="form-horizontal" method="post" role="form">
    <input id="entryId" type="hidden" name="id" value="{$e->id}">

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
            {include "bits/structStatusRadio.tpl" selected=$e->structStatus}
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

        <div class="form-group">
          <label class="col-md-2 control-label">etichete</label>
          <div class="col-md-10">
            <select name="tagIds[]" class="form-control select2Tags" multiple>
              {foreach $tagIds as $tagId}
                <option value="{$tagId}" selected></option>
              {/foreach}
            </select>
          </div>
        </div>

      </div>

      <div class="col-md-6">
        <div class="form-group">
          <label for="mainLexemeIds" class="col-md-2 control-label">
            lexeme
          </label>
          <div class="col-md-10">
            <select id="mainLexemeIds" name="mainLexemeIds[]" style="width: 100%" multiple>
              {foreach $mainLexemeIds as $l}
                <option value="{$l}" selected></option>
              {/foreach}
            </select>
          </div>
        </div>

        <div class="form-group">
          <label for="variantLexemeIds" class="col-md-2 control-label">
            variante
          </label>
          <div class="col-md-10">
            <select id="variantLexemeIds" name="variantLexemeIds[]" style="width: 100%" multiple>
              {foreach $variantLexemeIds as $l}
                <option value="{$l}" selected></option>
              {/foreach}
            </select>
            <div>
              Tipuri de model:
              {foreach $modelTypes as $mt}
                <span class="label label-default">{$mt}</span>
              {/foreach}

              <div class="btn-group pull-right">
                <button id="moveLexemesUp"
                  type="button"
                  class="btn btn-default btn-xs"
                  title="mută toate variantele la principale">
                  <i class="glyphicon glyphicon-chevron-up"></i>
                </button>
                <button id="moveLexemesDown"
                  type="button"
                  class="btn btn-default btn-xs"
                  title="mută toate principalele la variante">
                  <i class="glyphicon glyphicon-chevron-down"></i>
                </button>
              </div>
            </div>
          </div>
        </div>

        <div class="form-group">
          <label for="treeIds" class="col-md-2 control-label">
            arbori
          </label>
          <div class="col-md-10">
            <select name="treeIds[]" class="select2Trees" multiple>
              {foreach $treeIds as $t}
                <option value="{$t}" selected></option>
              {/foreach}
            </select>

            <div class="checkbox">
              <label title="cauzează ascunderea reclamelor">
                <input type="checkbox" name="adult" value="1" {if $e->adult}checked{/if}>
                conține definiții pentru adulți
              </label>
            </div>
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

    <button
      type="submit"
      class="btn btn-success"
      name="saveButton"
      {if !$canEdit}
      disabled
      title="doar structuriștii pot modifica intrările structurate"
      {/if}>
      <i class="glyphicon glyphicon-floppy-disk"></i>
      <u>s</u>alvează
    </button>

    <button
      type="button"
      class="btn btn-default"
      data-toggle="modal"
      data-target="#mergeModal"
      {if !$canEdit}
      disabled
      title="doar structuriștii pot modifica intrările structurate"
      {/if}>
      <i class="glyphicon glyphicon-resize-small"></i>
      unifică cu...
    </button>

    <button type="button" class="btn btn-default" data-toggle="modal" data-target="#cloneModal">
      <i class="glyphicon glyphicon-duplicate"></i>
      clonează...
    </button>

    <button
      type="submit"
      class="btn btn-default"
      name="createTree"
      {if !$canEdit}
      disabled
      title="doar structuriștii pot modifica intrările structurate"
      {/if}>
      <i class="glyphicon glyphicon-tree-deciduous"></i>
      creează un arbore de sensuri
    </button>

    {if $e->id}
      <a id="wikiLink"
        href="https://wiki.dexonline.ro/wiki/Intrare:{$e->id}?description={$e->description|escape}"
        class="btn btn-default"
        title="creează o pagină wiki pentru această intrare"
        target="_blank">
        <i class="glyphicon glyphicon-comment"></i>
        wiki
      </a>
    {/if}

    {if count($e->getLexemes())}
      <a class="btn btn-default" href="definitie/{$e->getMainLexeme()->formNoAccent}">
        <i class="glyphicon glyphicon-search"></i>
        caută
      </a>
    {/if}

    <a class="btn btn-link" href="{if $e->id}?id={$e->id}{/if}">
      renunță
    </a>

    <div class="pull-right">
      <button
        type="submit"
        class="btn btn-danger"
        name="delete"
        {if !$canDelete}
        disabled
        title="Nu puteți șterge intrarea deoarece ea îi este repartizată altui structurist"
        {/if}
      >
        <i class="glyphicon glyphicon-trash"></i>
        șterge
      </button>

      <button
        type="submit"
        class="btn btn-danger"
        name="deleteExt"
        {if !$canDelete}
        disabled
        title="Nu puteți șterge intrarea deoarece ea îi este repartizată altui structurist"
        {else}
        title="șterge intrarea, lexemele de tip T și arborii goi"
        {/if}
      >
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
            {if !User::can(User::PRIV_STRUCT)}
              <div class="alert alert-info" role="alert">
                Puteți selecta doar intrări care nu au fost deja structurate.
              </div>
            {/if}
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

  <div class="modal fade" id="associateModal" role="dialog">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <form method="post" role="form">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal">
              <span aria-hidden="true">&times;</span>
            </button>
            <h4 class="modal-title">Asociază definițiile cu...</h4>
          </div>

          <div class="modal-body">
            {* we need the entry ID so we know where to return *}
            <input type="hidden" name="id" value="{$e->id}">
            <input type="hidden" name="associateDefinitionIds" value="{$e->id}">
            <select id="associateEntryIds" name="associateEntryIds[]" class="form-control" multiple>
            </select>
          </div>

          <div class="modal-footer">
            <button type="submit" class="btn btn-primary" name="associateButton">
              <i class="glyphicon glyphicon-resize-small"></i>
              asociază
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
                <input type="checkbox" name="cloneDefinitions">
                copiază asocierile cu definiții
              </label>
            </div>
            <div class="checkbox">
              <label>
                <input type="checkbox" name="cloneLexemes">
                copiază asocierile cu lexeme
              </label>
            </div>
            <div class="checkbox">
              <label>
                <input type="checkbox" name="cloneTrees">
                copiază asocierile cu arbori
              </label>
            </div>
            <div class="checkbox">
              <label>
                <input type="checkbox" name="cloneStructurist">
                copiază starea structurării și structuristul
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
        <div class="panel-heading clearfix">
          {$t->description}

          <span class="tagList">
            {foreach $t->getTags() as $tag}
              {include "bits/tag.tpl" t=$tag}
            {/foreach}
          </span>

          <div class="btn-group pull-right">
            <a href="editTree.php?id={$t->id}" class="btn btn-sm btn-default">
              <i class="glyphicon glyphicon-pencil"></i>
              editează
            </a>
            <a href="?id={$e->id}&amp;deleteTreeId={$t->id}"
              class="btn btn-sm btn-danger {if !$t->canDelete()}disabled{/if}">
              <i class="glyphicon glyphicon-trash"></i>
              șterge
            </a>
          </div>
        </div>
        <div class="panel-body">
          {include "bits/editableMeaningTree.tpl"
            meanings=$t->getMeanings()
            id="meaningTree-{$t->id}"}
        </div>
      </div>
    {/foreach}

    <h3>Definiții asociate ({$searchResults|count})</h3>

    {if count($searchResults)}
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

          <div class="checkbox">
            <label>
              <input id="structurableFilter" type="checkbox"> numai definițiile de structurat
            </label>
          </div>

        </div>
      </form>

      <form method="post" role="form">
        {foreach $searchResults as $row}
          {$def=$row->definition}
          <div class="defWrapper
            {if $def->structured}structured{/if}
            {if $row->source->structurable}structurable{/if}"
            id="def_{$def->id}">
            <div>
              <span data-code="0" class="rep internal hiddenRep">{$def->internalRepAbbrev|escape}</span>
              <span data-code="1" class="rep hiddenRep">{$def->htmlAbbrev}</span>
              <span data-code="2" class="rep internal hiddenRep">{$def->internalRep|escape}</span>
              <span data-code="3" data-active class="rep">{HtmlConverter::convert($def)}</span>
              {foreach $row->tags as $t}
                {include "bits/tag.tpl"}
              {/foreach}
            </div>

            {include "bits/footnotes.tpl" footnotes=$def->getFootnotes()}

            {include "bits/definitionMenu.tpl"
              showSelectCheckbox=true
              showEntryToggles=true
              showHistory=true
              showPageModal=false
            }

          </div>
        {/foreach}

        <div>
          <button type="button"
            class="btn btn-default"
            data-toggle="modal"
            data-target="#associateModal">
            <i class="glyphicon glyphicon-resize-small"></i>
            asociază...
          </button>
          <button id="dissociateButton" type="submit" class="btn btn-default" name="dissociateButton">
            <i class="glyphicon glyphicon-resize-full"></i>
            disociază...
          </button>
        </div>

      </form>

    {/if}
  {/if}

  {include "bits/pageModal.tpl"}
{/block}
