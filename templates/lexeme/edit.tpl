{extends "layout-admin.tpl"}

{block "title"}Editare lexem: {$lexeme->form}{/block}

{block "content"}
  {$renameRelated=$renameRelated|default:false}

  <h3>
    Editare lexem: {$lexeme->form}
    <a
      class="float-end btn btn-sm btn-link"
      href="https://wiki.dexonline.ro/wiki/Editarea_lexemelor">
      {include "bits/icon.tpl" i=info}
      instrucțiuni
    </a>
  </h3>

  <script>
    canEdit = { 'paradigm': {$canEdit.paradigm} };
  </script>

  <form method="post">
    <div class="mb-3">

      <button type="submit"
        name="refreshButton"
        class="lexemeEditSaveButton btn btn-outline-secondary">
        {include "bits/icon.tpl" i=refresh}
        <u>r</u>eafișează
      </button>

      <button type="submit"
        name="saveButton"
        class="lexemeEditSaveButton btn btn-primary">
        {include "bits/icon.tpl" i=save}
        <u>s</u>alvează
      </button>

      {if $canEdit.general}
        <button type="button"
          class="btn btn-outline-secondary"
          data-bs-toggle="modal"
          data-bs-target="#cloneModal">
          {include "bits/icon.tpl" i=content_copy}
          clonează...
        </button>
      {/if}

      <a id="wikiLink"
        href="https://wiki.dexonline.ro/wiki/Lexem:{$lexeme->id}?description={$lexeme|escape}"
        class="btn btn-outline-secondary"
        title="creează o pagină wiki pentru acest lexem"
        target="_blank">
        {include "bits/icon.tpl" i=comment}
        wiki
      </a>

      <a
        class="btn btn-outline-secondary"
        href="definitie/{$lexeme->formNoAccent}"
        title="mergi la pagina publică de căutare">
        {include "bits/icon.tpl" i=search}
        caută
      </a>

      <a class="btn btn-link" href="?lexemeId={$lexeme->id}">renunță</a>

      {$canDelete=$lexeme->canDelete()}
      <button type="submit"
        name="deleteButton"
        onclick="return confirm('Confirmați ștergerea acestui lexem?');"
        class="btn btn-danger float-end"
        {if $canDelete != Lexeme::CAN_DELETE_OK}
        disabled
        title="{$canDelete}"
        {/if}>
        {include "bits/icon.tpl" i=delete}
        șterge
      </button>

    </div>

    <div class="card mb-3">

      <div class="card-header">Proprietăți</div>

      <div class="card-body">
        <input type="hidden" name="lexemeId" value="{$lexeme->id}">

        <div class="row">
          <div class="col-md-6">

            {include "bs/hf.tpl"
              inputId='lexemeForm'
              hfErrors=$errors.lexemeForm|default:null
              label='formă'
              name='lexemeForm'
              readonly=!$canEdit.form
              value=$lexeme->form}

            <div class="row mb-2">
              <div class="col-xl-10 offset-xl-2">
                {include "bs/checkbox.tpl"
                  name=renameRelated
                  label='redenumește și intrările și arborii care au această descriere'
                  checked=$renameRelated
                  divId=renameDiv
                  hidden=!$renameRelated}

                {include "bs/checkbox.tpl"
                  name=needsAccent
                  label='necesită accent'
                  cbErrors=$errors.needsAccent|default:null
                  checked=!$lexeme->noAccent}
              </div>
            </div>

            {include "bs/hf.tpl"
              label="descriere"
              name="lexemeDescription"
              placeholder="opțională, pentru diferențierea omonimelor"
              readonly=!$canEdit.description
              value=$lexeme->description}

            {include "bs/hf.tpl"
              label="număr"
              name="lexemeNumber"
              placeholder="opțional, pentru numerotarea omonimelor"
              readonly=!$canEdit.general
              type="number"
              value=$lexeme->number}

            {if $homonyms}
              <div class="row mb-2">
                <label class="col-xl-2">omonime</label>
                <div class="col-xl-10">
                  <ul class="list-inline list-inline-bullet">
                    {foreach $homonyms as $h}
                      <li class="list-inline-item">
                        {include "bits/lexemeLink.tpl" lexeme=$h}
                      </li>
                    {/foreach}
                  </ul>
                </div>
              </div>
            {/if}

            <fieldset>
              <legend class="fs-5">intrări pentru care lexemul este:</legend>

              {foreach from=$entryIds key=k item=e}
                <div class="row mb-2">
                  <label class="col-xl-2 col-form-label">{$assocEntry[{$k}]}</label>
                  <div class="col-xl-10">
                    <select id="entryIds[{$k}]" name="entryIds[{$k}][]" class="form-select" multiple>
                      {foreach $e as $eid}
                        <option value="{$eid}" selected></option>
                      {/foreach}
                    </select>
                  </div>
                </div>
              {/foreach}
            </fieldset>

            {if $compoundIds}
              <div class="row mb-2">
                <label for="compoundIds" class="col-xl-2">compuse</label>
                <div class="col-xl-10">
                  <div class="form-control overflown">
                    {foreach $compoundIds as $c}
                      <div>
                        {include "bits/lexemeLink.tpl" boxed=true lexeme=$c}
                      </div>
                    {/foreach}
                  </div>
                </div>
              </div>
            {/if}

          </div>

          <div class="col-md-6">

            {include "bs/hf.tpl"
              label="silabații"
              name="hyphenations"
              placeholder="opționale, despărțite prin virgule"
              readonly=!$canEdit.hyphenations
              value=$lexeme->hyphenations}

            {include "bs/hf.tpl"
              label="pronunții"
              name="pronunciations"
              placeholder="opționale, despărțite prin virgule"
              readonly=!$canEdit.pronunciations
              value=$lexeme->pronunciations}

            <div class="row mb-2">
              <label for="tagIds" class="col-xl-2 col-form-label">etichete</label>
              <div class="col-xl-10">
                <select id="tagIds" name="tagIds[]" class="form-select select2Tags" multiple>
                  {foreach $lexeme->getTagIds() as $tagId}
                    <option value="{$tagId}" selected></option>
                  {/foreach}
                </select>

                {include "bits/frequentObjects.tpl"
                  align='text-start'
                  name="lexemeTags"
                  type="tags"
                  target="#tagIds"}

                {include "bs/checkbox.tpl"
                  name=stopWord
                  label='ignoră la căutările full-text'
                  checked=$lexeme->stopWord
                  disabled=!$canEdit.stopWord}
              </div>
            </div>

          </div>
        </div>
      </div>
    </div>

    <div class="card mb-3">

      <div class="card-header">Model de flexiune</div>

      <div class="card-body">

        <div class="row">
          <div class="col-md-7">

            <div class="row mb-2">
              <label class="col-md-3 form-label">lexem compus</label>

              <div class="col-md-9 ps-0">
                {include "bs/checkbox.tpl"
                  name=compound
                  label=''
                  checked=$lexeme->compound}
              </div>
            </div>

            {* Fields for simple lexemes *}
            <div id="modelDataSimple" {if $lexeme->compound}hidden{/if}>
              <div class="row mb-2">
                <label class="col-md-3 col-form-label">tip + număr</label>

                <div class="col-md-9 row row-cols-md-auto gx-1">
                  <div class="col">
                    {include "bits/modelDropDown.tpl"
                      selectedModelType=$lexeme->modelType
                      selectedModelNumber=$lexeme->modelNumber}
                  </div>

                  <div class="col">
                    <input
                      type="text"
                      class="form-control {if isset($errors.restriction)}is-invalid{/if}"
                      name="restriction"
                      value="{$lexeme->restriction}"
                      size="5"
                      placeholder="restricții">
                    {include "bits/fieldErrors.tpl" errors=$errors.restriction|default:null}
                  </div>
                </div>
              </div>

              <div class="row mb-2">
                <div class="col-md-9 offset-md-3">
                  <select class="similarLexeme"></select>
                </div>
              </div>
            </div>

            {* Fields for compound lexemes *}
            <div id="modelDataCompound" {if !$lexeme->compound}hidden{/if}>

              <div class="row mb-2">
                <label class="col-md-3 col-form-label">tip</label>

                <div class="col-md-9 row row-cols-md-auto gx-1">
                  <div class="col">
                    <select name="compoundModelType" class="form-select">
                      {foreach $modelTypes as $mt}
                        <option value="{$mt->code}"
                          {if $lexeme->modelType == $mt->code}selected{/if}>
                          {$mt->code}
                        </option>
                      {/foreach}
                    </select>
                  </div>

                  <div class="col">
                    <input
                      type="text"
                      class="form-control {if isset($errors.compoundRestriction)}is-invalid{/if}"
                      name="compoundRestriction"
                      value="{$lexeme->restriction}"
                      size="5"
                      placeholder="restricții">
                    {include "bits/fieldErrors.tpl" errors=$errors.compoundRestriction|default:null}
                  </div>
                </div>
              </div>

              <div class="row mb-2">
                <label class="col-md-3 col-form-label">compus din</label>

                <div class="col-md-9 px-0">
                  <div id="fragmentContainer">
                    {include "bits/fragment.tpl" id="stem"}
                    {foreach $lexeme->getFragments() as $fragment}
                      {include "bits/fragment.tpl"}
                    {/foreach}
                  </div>

                  <button
                    id="addFragmentButton"
                    class="btn btn-outline-secondary btn-sm"
                    type="button">
                    {include "bits/icon.tpl" i=add}
                    adaugă
                  </button>
                  <button
                    id="autoFragmentButton"
                    class="btn btn-outline-secondary btn-sm"
                    type="button">
                    {include "bits/icon.tpl" i=content_cut}
                    autocompletează
                  </button>
                </div>
              </div>

            </div>

          </div>

          <div class="col-md-5">

            <div class="row mb-2">
              <label class="col-md-3 col-form-label">surse</label>
              <div class="col-md-9">
                <select id="sourceIds"
                  class="form-select"
                  name="sourceIds[]"
                  multiple
                  {if !$canEdit.sources}disabled{/if}>
                  {foreach $sourceIds as $sId}
                    <option value="{$sId}" selected></option>
                  {/foreach}
                </select>

                {include "bits/frequentObjects.tpl"
                  name="lexemeSources"
                  type="sources"
                  target="#sourceIds"}

              </div>
            </div>

            {include "bs/hf.tpl"
              col=3
              label="precizări"
              name="notes"
              placeholder="explicații despre sursa flexiunii"
              readonly=!$canEdit.tags
              value=$lexeme->notes}

            <div class="row mb-2">
              <div class="col-md-9 offset-md-3">
                {include "bs/checkbox.tpl"
                  name=hasApheresis
                  label='admite afereză'
                  checked=$lexeme->hasApheresis}

                {include "bs/checkbox.tpl"
                  name=hasApocope
                  label='admite apocopă'
                  checked=$lexeme->hasApocope}
              </div>
            </div>

          </div>
        </div>
      </div>
    </div>

    <div class="card mb-3">
      <div class="card-header">Paradigmă</div>
      <div class="card-body">
        {include "paradigm/paradigm.tpl" lexeme=$lexeme}
      </div>
    </div>

    {foreach from=$searchResults key=k item=d}
      <div class="card mb-3">
        <div class="card-header">Definiții pentru intrările unde este lexem {$assocEntry[{$k}]} ({$searchResults[$k]|count})</div>
        <div class="card-body">
          {foreach $searchResults[$k] as $row}
            {include "bits/definition.tpl" showStatus=1}
          {/foreach}
        </div>
      </div>
    {/foreach}

    {include "bits/cloneModal.tpl" object="Lexeme" desc="lexem"}

  </form>
{/block}
