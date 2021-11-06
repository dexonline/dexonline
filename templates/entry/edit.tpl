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

  <div id="isStructurist" hidden>{User::can(User::PRIV_STRUCT)}</div>

  <h3>
    {if $e->id}
      Editează intrarea
    {else}
      Adaugă o intrare
    {/if}
  </h3>

  {include "bits/phpConstants.tpl"}

  <form class="mb-3" method="post">
    <input id="entryId" type="hidden" name="id" value="{$e->id}">

    <div class="row mb-2">

      <div class="col-md-6">
        <div class="row mb-2">
          <label for="description" class="col-md-2 col-form-label">
            descriere
          </label>
          <div class="col-md-10">
            <div>
              <input type="text"
                class="form-control {if isset($errors.description)}is-invalid{/if}"
                id="description"
                name="description"
                value="{$e->description}">
              {include "bits/fieldErrors.tpl" errors=$errors.description|default:null}
            </div>

            {include "bs/checkbox.tpl"
              name=renameTrees
              label='redenumește și arborii la fel'
              checked=$renameTrees
              divId=renameDiv
              hidden=!$renameTrees}
          </div>
        </div>

        <div class="row mb-2">
          <label for="structStatus" class="col-md-2 form-label">
            structurare
          </label>
          <div class="col-md-10">
            {include "bits/structStatusRadio.tpl" selected=$e->structStatus}
          </div>
        </div>

        <div class="row mb-2">
          <label for="structuristId" class="col-md-2 col-form-label">
            structurist
          </label>
          <div class="col-md-10">
            <select
              id="structuristId"
              name="structuristId"
              class="form-select {if isset($errors.structuristId)}is-invalid{/if}">
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

        <div class="row mb-2">
          <label class="col-md-2 col-form-label">etichete</label>
          <div class="col-md-10">
            <select name="tagIds[]" class="form-select select2Tags" multiple>
              {foreach $tagIds as $tagId}
                <option value="{$tagId}" selected></option>
              {/foreach}
            </select>
          </div>
        </div>

      </div>

      <div class="col-md-6">
        <div class="row mb-2">
          <label for="mainLexemeIds" class="col-md-2 col-form-label">
            lexeme
          </label>
          <div class="col-md-10">
            <select
              id="mainLexemeIds"
              class="form-select {if isset($errors.mainLexemeIds)}is-invalid{/if}"
              name="mainLexemeIds[]"
              style="width: 100%"
              multiple>
              {foreach $mainLexemeIds as $l}
                <option value="{$l}" selected></option>
              {/foreach}
            </select>
            {include "bits/fieldErrors.tpl" errors=$errors.mainLexemeIds|default:null}
          </div>
        </div>

        <div class="row mb-2">
          <label for="variantLexemeIds" class="col-md-2 col-form-label">
            variante
          </label>
          <div class="col-md-10">
            <select id="variantLexemeIds" name="variantLexemeIds[]" style="width: 100%" multiple>
              {foreach $variantLexemeIds as $l}
                <option value="{$l}" selected></option>
              {/foreach}
            </select>
            <div class="mt-1">
              Tipuri de model:
              {foreach $modelTypes as $mt}
                <span class="badge badge-muted">{$mt}</span>
              {/foreach}

              <div class="float-end">
                <button id="moveLexemesUp"
                  type="button"
                  class="btn btn-link btn-sm"
                  title="mută toate variantele la principale">
                  {include "bits/icon.tpl" i=expand_less}
                </button>
                <button id="moveLexemesDown"
                  type="button"
                  class="btn btn-link btn-sm"
                  title="mută toate principalele la variante">
                  {include "bits/icon.tpl" i=expand_more}
                </button>
              </div>
            </div>
          </div>
        </div>

        <div class="row mb-2">
          <label for="treeIds" class="col-md-2 col-form-label">
            arbori
          </label>
          <div class="col-md-10">
            <select name="treeIds[]" class="select2Trees" multiple>
              {foreach $treeIds as $t}
                <option value="{$t}" selected></option>
              {/foreach}
            </select>

            {include "bs/checkbox.tpl"
              name=adult
              label='conține definiții pentru adulți'
              checked=$e->adult}

            {include "bs/checkbox.tpl"
              name=multipleMains
              label='conține lexeme principale multiple'
              cbErrors=$errors.multipleMains|default:null
              checked=$e->multipleMains}
          </div>
        </div>

        {if $homonyms}
          <div class="row mb-2">
            <label class="col-md-2 col-form-label">omonime</label>
            <div class="col-md-10">
              <div class="form-control overflown">
                {foreach $homonyms as $h}
                  {include "bits/entry.tpl" boxed=true entry=$h editLink=true}
                {/foreach}
              </div>
            </div>
          </div>
        {/if}
      </div>

    </div>

    <button
      type="submit"
      class="btn btn-primary"
      name="saveButton"
      {if !$canEdit}
      disabled
      title="doar structuriștii pot modifica intrările structurate"
      {/if}>
      {include "bits/icon.tpl" i=save}
      <u>s</u>alvează
    </button>

    <button
      type="button"
      class="btn btn-outline-secondary"
      data-bs-toggle="modal"
      data-bs-target="#mergeModal"
      {if !$canEdit}
      disabled
      title="doar structuriștii pot modifica intrările structurate"
      {/if}>
      {include "bits/icon.tpl" i=merge_type}
      unifică cu...
    </button>

    <button
      type="button"
      class="btn btn-outline-secondary"
      data-bs-toggle="modal"
      data-bs-target="#cloneModal">
      {include "bits/icon.tpl" i=content_copy}
      clonează...
    </button>

    <button
      type="submit"
      class="btn btn-outline-secondary"
      name="createTree"
      {if !$canEdit}
      disabled
      title="doar structuriștii pot modifica intrările structurate"
      {/if}>
      {include "bits/icon.tpl" i=park}
      creează un arbore de sensuri
    </button>

    {if $e->id}
      <a id="wikiLink"
        href="https://wiki.dexonline.ro/wiki/Intrare:{$e->id}?description={$e->description|escape}"
        class="btn btn-outline-secondary"
        title="creează o pagină wiki pentru această intrare"
        target="_blank">
        {include "bits/icon.tpl" i=comment}
        wiki
      </a>
    {/if}

    {if count($e->getLexemes())}
      <a class="btn btn-outline-secondary" href="definitie/{$e->getMainLexeme()->formNoAccent}">
        {include "bits/icon.tpl" i=search}
        caută
      </a>
    {/if}

    <a class="btn btn-link" href="{if $e->id}?id={$e->id}{/if}">
      renunță
    </a>

    <div class="float-end">
      <button
        type="submit"
        class="btn btn-outline-danger"
        name="delete"
        {if !$canDelete}
        disabled
        title="Nu puteți șterge intrarea deoarece ea îi este repartizată altui structurist"
        {/if}
      >
        {include "bits/icon.tpl" i=delete}
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
        {include "bits/icon.tpl" i=delete}
        șterge extins
      </button>
    </div>
  </form>

  <div class="modal fade" id="mergeModal" role="dialog">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <form method="post" role="form">
          <div class="modal-header">
            <h4 class="modal-title">Unifică intrarea cu...</h4>
            <button
              type="button"
              class="btn-close"
              data-bs-dismiss="modal"
              aria-label="Close">
            </button>
          </div>

          <div class="modal-body">
            {if !User::can(User::PRIV_STRUCT)}
              {notice icon="info"}
                Puteți selecta doar intrări care nu au fost deja structurate.
              {/notice}
            {/if}
            <input type="hidden" name="id" value="{$e->id}">
            <select id="mergeEntryId" name="mergeEntryId" class="form-select">
            </select>
          </div>

          <div class="modal-footer">
            <button type="submit" class="btn btn-primary" name="mergeButton">
              {include "bits/icon.tpl" i=merge_type}
              unifică
            </button>
            <button type="button" class="btn btn-link" data-bs-dismiss="modal">renunță</button>
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
            <h4 class="modal-title">Asociază definițiile cu...</h4>
            <button
              type="button"
              class="btn-close"
              data-bs-dismiss="modal"
              aria-label="Close">
            </button>
          </div>

          <div class="modal-body">
            {* we need the entry ID so we know where to return *}
            <input type="hidden" name="id" value="{$e->id}">
            <input type="hidden" name="associateDefinitionIds" value="{$e->id}">
            <select id="associateEntryIds" name="associateEntryIds[]" class="form-select" multiple>
            </select>
          </div>

          <div class="modal-footer">
            <button
              type="submit"
              class="btn btn-primary"
              name="associateButton"
              title="asociază definițiile cu noua intrare și lasă-le asociate și aici">
              {include "bits/icon.tpl" i=content_copy}
              copiază
            </button>
            <button
              type="submit"
              class="btn btn-primary"
              name="moveButton"
              title="asociază definițiile cu noua intrare și disociază-le de aici">
              {include "bits/icon.tpl" i=content_cut}
              mută
            </button>
            <button type="button" class="btn btn-link" data-bs-dismiss="modal">renunță</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  {include "bits/cloneModal.tpl" object="Entry" desc="intrare"}

  {if $e->id}
    <h3>Arbori de sensuri asociați ({$e->getTrees()|count})</h3>

    <form class="row row-cols-lg-auto mb-3">
      <div class="col-12">
        <select id="treeFilterSelect" class="form-select">
          <option value="{Tree::ST_VISIBLE}">doar arborii vizibili</option>
          <option value="{Tree::ST_HIDDEN}">doar arborii ascunși</option>
          <option value="-1">toți arborii</option>
        </select>
      </div>
    </form>

    {foreach $e->getTrees() as $t}
      <div class="card mb-3 tree tree-status-{$t->status}">
        <div class="card-header d-flex justify-content-between align-items-center">

          <span>
            {$t->description}

            <span class="ms-2">
              {foreach $t->getTags() as $tag}
                {include "bits/tag.tpl" t=$tag}
              {/foreach}
            </span>
          </span>

          <div>
            <a href="{Router::link('tree/edit')}?id={$t->id}" class="btn btn-sm btn-outline-secondary">
              {include "bits/icon.tpl" i=edit}
              editează
            </a>
            <a href="?id={$e->id}&amp;deleteTreeId={$t->id}"
              class="btn btn-sm btn-danger {if !$t->canDelete()}disabled{/if}">
              {include "bits/icon.tpl" i=delete}
              șterge
            </a>
          </div>
        </div>
        <div class="card-body">
          {include "bits/editableMeaningTree.tpl"
            meanings=$t->getMeanings()
            id="meaningTree-{$t->id}"}
        </div>
      </div>
    {/foreach}

    <h3>Definiții asociate ({$searchResults|count})</h3>

    {if count($searchResults)}
      <form class="row row-cols-lg-auto g-1 mb-2 d-flex align-items-center">
        <div class="col-12">
          <select id="defFilterSelect" class="form-select">
            <option value="">toate</option>
            <option value="structured">structurate</option>
            <option value="unstructured">nestructurate</option>
          </select>
        </div>

        <div class="col-12">
          <select class="toggleRepSelect form-select" data-order="1">
            <option value="0">text</option>
            <option value="1" selected>html</option>
          </select>
        </div>

        <div class="col-12">
          <select class="toggleRepSelect form-select" data-order="2">
            <option value="0">expandat</option>
            <option value="1" selected>abreviat</option>
          </select>
        </div>

        <div class="col-12">
          {include "bs/checkbox.tpl"
            inputId=structurableFilter
            name=false
            label='numai definițiile de structurat'}
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
          <button
            type="button"
            class="btn btn-outline-secondary"
            data-bs-toggle="modal"
            data-bs-target="#associateModal">
            asociază...
          </button>
          <button
            id="dissociateButton"
            type="submit"
            class="btn btn-outline-secondary"
            name="dissociateButton">
            disociază...
          </button>
        </div>

      </form>

    {/if}
  {/if}
  {include "bits/definitionTypoForm.tpl"}

  {include "bits/pageModal.tpl"}
{/block}
