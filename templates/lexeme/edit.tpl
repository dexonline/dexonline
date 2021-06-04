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
        class="lexemeEditSaveButton btn btn-light">
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
          class="btn btn-light"
          data-bs-toggle="modal"
          data-bs-target="#cloneModal">
          {include "bits/icon.tpl" i=content_copy}
          clonează...
        </button>
      {/if}

      <a id="wikiLink"
        href="https://wiki.dexonline.ro/wiki/Lexem:{$lexeme->id}?description={$lexeme|escape}"
        class="btn btn-light"
        title="creează o pagină wiki pentru acest lexem"
        target="_blank">
        {include "bits/icon.tpl" i=comment}
        wiki
      </a>

      <a
        class="btn btn-light"
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

            <div class="row mb-2">
              <label for="lexemeForm" class="col-md-2 col-form-label">formă</label>
              <div class="col-md-10">
                <input type="text"
                  class="form-control"
                  id="lexemeForm"
                  name="lexemeForm"
                  value="{$lexeme->form|escape}"
                  {if !$canEdit.form}readonly{/if}>

                <div id="renameDiv"
                  class="form-check"
                  {if !$renameRelated}hidden{/if}>
                  <label class="form-check-label">
                    <input
                      type="checkbox"
                      class="form-check-input"
                      name="renameRelated"
                      value="1"
                      {if $renameRelated}checked{/if}>
                    redenumește și intrările și arborii care au această descriere
                  </label>
                </div>

                <div class="form-check">
                  <label class="form-check-label">
                    <input
                      type="checkbox"
                      class="form-check-input"
                      name="needsAccent"
                      value="1"
                      {if !$lexeme->noAccent}checked{/if}>
                    necesită accent
                  </label>
                </div>
              </div>
            </div>

            {include "bits/fhf.tpl"
              field="lexemeDescription"
              value=$lexeme->description
              label="descriere"
              placeholder="opțională, pentru diferențierea omonimelor"
              readonly=!$canEdit.description}

            {include "bits/fhf.tpl"
              field="lexemeNumber"
              type="number"
              value=$lexeme->number
              label="număr"
              placeholder="opțional, pentru numerotarea omonimelor"
              readonly=!$canEdit.general}

            {if $homonyms}
              <div class="row mb-2">
                <label class="col-md-2">omonime</label>
                <div class="col-md-10">
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

            <div class="row mb-1">
              <div class="clearfix col-md-10 offset-md-2">
                <label>intrări pentru care lexemul este:</label>
              </div>
            </div>

            {foreach from=$entryIds key=k item=e}
              <div class="row mb-2">
                <label class="col-md-2 col-form-label">{$assocEntry[{$k}]}</label>
                <div class="col-md-10">
                  <select id="entryIds[{$k}]" name="entryIds[{$k}][]" class="form-select" multiple>
                    {foreach $e as $eid}
                      <option value="{$eid}" selected></option>
                    {/foreach}
                  </select>
                </div>
              </div>
            {/foreach}

            {if $compoundIds}
              <div class="row mb-2">
                <label for="compoundIds" class="col-md-2">compuse</label>
                <div class="col-md-10">
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

            {include "bits/fhf.tpl"
              field="hyphenations"
              value=$lexeme->hyphenations
              label="silabații"
              placeholder="opționale, despărțite prin virgule"
              readonly=!$canEdit.hyphenations}

            {include "bits/fhf.tpl"
              field="pronunciations"
              value=$lexeme->pronunciations
              label="pronunții"
              placeholder="opționale, despărțite prin virgule"
              readonly=!$canEdit.pronunciations}

            <div class="row mb-2">
              <label for="tagIds" class="col-md-2 col-form-label">etichete</label>
              <div class="col-md-10">
                <select id="tagIds" name="tagIds[]" class="form-select select2Tags" multiple>
                  {foreach $lexeme->getTagIds() as $tagId}
                    <option value="{$tagId}" selected></option>
                  {/foreach}
                </select>

                {include "bits/frequentObjects.tpl"
                  name="lexemeTags"
                  type="tags"
                  target="#tagIds"}

                <div class="form-check">
                  <label class="form-check-label">
                    <input
                      type="checkbox"
                      class="form-check-input"
                      name="stopWord"
                      value="1"
                      {if $lexeme->stopWord}checked{/if}
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

    <div class="card mb-3">

      <div class="card-header">Model de flexiune</div>

      <div class="card-body">

        <div class="row">
          <div class="col-md-7">

            <div class="row mb-2">
              <label class="col-md-3 form-label">lexem compus</label>

              <div class="col-md-9">
                <div class="form-check">
                  <input
                    type="checkbox"
                    class="form-check-input"
                    name="compound"
                    value="1"
                    {if $lexeme->compound}checked{/if}>
                </div>
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
                      class="form-control"
                      name="restriction"
                      value="{$lexeme->restriction}"
                      size="5"
                      placeholder="restricții">
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
                      class="form-control"
                      name="compoundRestriction"
                      value="{$lexeme->restriction}"
                      size="5"
                      placeholder="restricții">
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

                  <button id="addFragmentButton" class="btn btn-light btn-sm" type="button">
                    {include "bits/icon.tpl" i=add}
                    adaugă
                  </button>
                  <button id="autoFragmentButton" class="btn btn-light btn-sm" type="button">
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

            {include "bits/fhf.tpl"
              field="notes"
              value=$lexeme->notes
              label="precizări"
              placeholder="explicații despre sursa flexiunii"
              col=3
              readonly=!$canEdit.tags}

            <div class="row mb-2">
              <div class="col-md-9 offset-md-3">
                <div class="form-check">
                  <label class="form-check-label">
                    <input
                      type="checkbox"
                      class="form-check-input"
                      name="hasApheresis"
                      value="1"
                      {if $lexeme->hasApheresis}checked{/if}>
                    admite afereză
                  </label>
                </div>

                <div class="form-check">
                  <label class="form-check-label">
                    <input
                      type="checkbox"
                      class="form-check-input"
                      name="hasApocope"
                      value="1"
                      {if $lexeme->hasApocope}checked{/if}>
                    admite apocopă
                  </label>
                </div>
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
