{extends "layout-admin.tpl"}

{block "title"}Editare lexem: {$lexeme->form}{/block}

{block "content"}
  {$renameRelated=$renameRelated|default:false}
  {$isCompound = $lexeme->compound}
  <h3>
    Editare lexem: {$lexeme->form}
    <span class="pull-right">
      <small>
        <a href="https://wiki.dexonline.ro/wiki/Editarea_lexemelor">
          <i class="glyphicon glyphicon-question-sign"></i>
          instrucțiuni
        </a>
      </small>
    </span>
  </h3>

  <script>
    canEdit = { 'paradigm': {$canEdit.paradigm} };
  </script>

  <form method="post">
    <div class="form-group">

      <button id="refreshButton"
        name="refreshButton"
        class="btn btn-primary">
        <i class="glyphicon glyphicon-refresh"></i>
        <u>r</u>eafișează
      </button>

      <button type="submit"
        name="saveButton"
        class="btn btn-success">
        <i class="glyphicon glyphicon-floppy-disk"></i>
        <u>s</u>alvează
      </button>

      {if $canEdit.general}
        <button type="button"
          class="btn btn-default"
          data-toggle="modal" data-target="#cloneModal">
          <i class="glyphicon glyphicon-duplicate"></i>
          clonează
        </button>
      {/if}

      <a id="wikiLink"
        href="https://wiki.dexonline.ro/wiki/Lexem:{$lexeme->id}?description={$lexeme|escape}"
        class="btn btn-default"
        title="creează o pagină wiki pentru acest lexem"
        target="_blank">
        <i class="glyphicon glyphicon-comment"></i>
        wiki
      </a>

      <a class="btn btn-default" href="/definitie/{$lexeme->formNoAccent}">
        <i class="glyphicon glyphicon-search"></i>
        caută
      </a>

      <a class="btn btn-link" href="{$lexeme->id}">renunță</a>

      {$canDelete=$lexeme->canDelete()}
      <button type="submit"
        name="deleteButton"
        onclick="return confirm('Confirmați ștergerea acestui lexem?');"
        class="btn btn-danger pull-right"
        {if $canDelete != Lexeme::CAN_DELETE_OK}
        disabled
        title="{$canDelete}"
        {/if}>
        <i class="glyphicon glyphicon-trash"></i>
        șterge
      </button>

    </div>

    <div class="panel panel-default">

      <div class="panel-heading">Proprietăți</div>

      <div class="panel-body">
        <input type="hidden" name="lexemeId" value="{$lexeme->id}">

        <div class="row">
          <div class="col-md-6 form-horizontal">

            <div class="form-group">
              <label for="lexemeForm" class="col-md-2 control-label">formă</label>
              <div class="col-md-10">
                <input type="text"
                  class="form-control"
                  id="lexemeForm"
                  name="lexemeForm"
                  value="{$lexeme->form|escape}"
                  {if !$canEdit.form}readonly{/if}>

                <div id="renameDiv"
                  class="checkbox {if !$renameRelated}hidden{/if}">
                  <label>
                    <input type="checkbox"
                      name="renameRelated"
                      value="1"
                      {if $renameRelated}checked{/if}>
                    redenumește și intrările și arborii care au această descriere
                  </label>
                </div>

                <div class="checkbox">
                  <label>
                    <input type="checkbox" name="needsAccent" value="1" {if !$lexeme->noAccent}checked{/if}>
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
              <div class="form-group">
                <label class="col-md-2">omonime</label>
                <div class="col-md-10">

                  {foreach $homonyms as $h}
                    <div>
                      {include "bits/lexemeLink.tpl" lexeme=$h}
                    </div>
                  {/foreach}

                </div>
              </div>
            {/if}
            <div class="row">
              <div class="clearfix col-md-10 col-md-offset-2">
                <label>intrări pentru care lexemul este:</label>
              </div>
            </div>

            {foreach from=$entryIds key=k item=e}
              <div class="form-group">
                <label class="col-md-2 control-label">{$assocEntry[{$k}]}</label>
                <div class="col-md-10">
                  <select id="entryIds[{$k}]" name="entryIds[{$k}][]" class="form-control" multiple>
                    {foreach $e as $eid}
                      <option value="{$eid}" selected></option>
                    {/foreach}
                  </select>
                </div>
              </div>
            {/foreach}

            {if $compoundIds}
            <div class="form-group">
              <label class="col-md-2 control-label">compuse</label>
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

          <div class="col-md-6 form-horizontal">

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

            <div class="form-group">
              <label for="tagIds" class="col-md-2 control-label">etichete</label>
              <div class="col-md-10">
                <select id="tagIds" name="tagIds[]" class="form-control select2Tags" multiple>
                  {foreach $lexeme->getTagIds() as $tagId}
                    <option value="{$tagId}" selected></option>
                  {/foreach}
                </select>

                {include "bits/frequentObjects.tpl"
                  name="lexemeTags"
                  type="tags"
                  target="#tagIds"}

                <div class="checkbox">
                  <label>
                    <input type="checkbox"
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

    <div class="panel panel-default">

      <div class="panel-heading">Model de flexiune</div>

      <div id="paradigmOptions" class="panel-body">

        <div class="row">
          <div class="col-md-7 form-horizontal">

            <div class="form-group">
              <label class="col-md-3 control-label">lexem compus</label>

              <div class="col-md-9">
                <div class="checkbox">
                  <label>
                    <input type="checkbox"
                      name="compound"
                      value="1"
                      {if $lexeme->compound}checked{/if}>
                  </label>
                </div>
              </div>
            </div>

            {* Fields for lexemes *}
            <div id="modelData">
              <div class="form-group">
                <label id="tip" class="col-md-3 control-label">
                  tip {if !$lexeme->compound}+ număr{/if}
                </label>
                <div class="col-md-9 form-inline">
                  <span data-model-dropdown>
                    {include "bits/modelTypeDropdown.tpl"}
                    {include "bits/modelNumberDropdown.tpl"}

                    <div class="input-group">
                      <input
                        type="text"
                        class="form-control"
                        name="restriction"
                        value="{$lexeme->restriction}"
                        size="5"
                        placeholder="restricții">
                      <div class="input-group-btn">
                        <button id="load" type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                          <span class="caret"></span>
                        </button>
                        <div id="restrictionMenu" class="dropdown-menu dropdown-menu-right">
                        {* menu is fetched through ajax calls based on modelType *}
                        </div>
                      </div><!-- /btn-group -->
                    </div><!-- /input-group -->
                  </span>
                </div>
              </div>
            </div>

            {* Fields for simple lexemes *}
            <div id="modelDataSimple" {if $lexeme->compound}style="display: none"{/if}>
              <div class="form-group">
                <div class="col-md-offset-3 col-md-9">
                  <select class="similarLexeme"></select>
                </div>
              </div>
            </div>

            {* Fields for compound lexemes *}
            <div id="modelDataCompound" {if !$lexeme->compound}style="display: none"{/if}>
              <div class="form-group">
                <label class="col-md-3 control-label">compus din</label>

                <div class="col-md-9">
                  <div id="fragmentContainer">
                    {include "bits/fragment.tpl" id="stem"}
                    {foreach $lexeme->getFragments() as $fragment}
                      {include "bits/fragment.tpl"}
                    {/foreach}
                  </div>

                  <div class="voffset2"></div>

                  <button id="addFragmentButton" class="btn btn-default btn-sm" type="button">
                    <i class="glyphicon glyphicon-plus"></i>
                    adaugă
                  </button>
                  <button id="autoFragmentButton" class="btn btn-default btn-sm" type="button">
                    <i class="glyphicon glyphicon-scissors"></i>
                    autocompletează
                  </button>
                </div>
              </div>
            </div>

          </div>

          <div class="col-md-5 form-horizontal">

            <div class="form-group">
              <label class="col-md-2 control-label">surse</label>
              <div class="col-md-10">
                <select id="sourceIds"
                  class="form-control"
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
              readonly=!$canEdit.tags}

            <div class="form-group">
              <div class="col-md-offset-2 col-md-10">
                <div class="checkbox">
                  <label>
                    <input type="checkbox"
                      name="hasApheresis"
                      value="1"
                      {if $lexeme->hasApheresis}checked{/if}>
                    admite afereză
                  </label>
                </div>

                <div class="checkbox">
                  <label>
                    <input type="checkbox"
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

    <div class="panel panel-default">
      <div class="panel-heading clearfix">Paradigmă
        <button type="button" class="btn btn-xs btn-primary ld-ext-left pull-right" id="refreshParadigm"> reafișează
          <div class="ld ld-ring ld-spin-fast"></div>
        </button>
      </div>
      <div class="panel-body">
        <div id="paradigmContent">
          {* here we replace what we get from ajax on refreshButton.click or refreshParadigm.click *}
          {include "paradigm/paradigm.tpl" lexeme=$lexeme}
        </div>
      </div>
    </div>

    {foreach from=$searchResults key=k item=d}
      <div class="panel panel-default">
        <div class="panel-heading">Definiții pentru intrările unde este lexem {$assocEntry[{$k}]} ({$searchResults[$k]|count})</div>
        <div class="panel-body panel-admin">
          {foreach $searchResults[$k] as $row}
            {include "bits/definition.tpl" showStatus=1}
          {/foreach}
        </div>
      </div>
    {/foreach}

    {include "bits/cloneModal.tpl" object="Lexeme" desc="lexem"}

  </form>
{/block}
