{extends "layout-admin.tpl"}

{block "title"}Editare lexem: {$lexeme->form}{/block}

{block "content"}
  {$renameRelated=$renameRelated|default:false}

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
   canEdit = { 'paradigm': {$canEdit.paradigm}, 'loc': {$canEdit.loc} };
  </script>

  <form action="lexemEdit.php" method="post">
    <div class="form-group">

      <button type="submit"
              name="refreshButton"
              class="lexemEditSaveButton btn btn-primary">
        <i class="glyphicon glyphicon-refresh"></i>
        <u>r</u>eafișează
      </button>

      <button type="submit"
              name="saveButton"
              class="lexemEditSaveButton btn btn-success">
        <i class="glyphicon glyphicon-floppy-disk"></i>
        <u>s</u>alvează
      </button>

      {if $canEdit.general}
        <button type="submit"
                name="cloneButton"
                class="btn btn-default">
          <i class="glyphicon glyphicon-duplicate"></i>
          clonează
        </button>
      {/if}

      <a href="https://wiki.dexonline.ro/wiki/Lexem:{$lexeme->id}?description={$lexem|escape}"
         class="btn btn-default"
         target="_blank">
        <i class="glyphicon glyphicon-comment"></i>
        wiki
      </a>

      <a class="btn btn-default" href="{$wwwRoot}definitie/{$lexeme->formNoAccent}">
        <i class="glyphicon glyphicon-search"></i>
        caută
      </a>

      <a class="btn btn-link" href="?lexemeId={$lexeme->id}">renunță</a>

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
                      {include "bits/lexemeLink.tpl" lexem=$h}
                    </div>
                  {/foreach}

                </div>
              </div>
            {/if}

            <div class="form-group">
              <label for="entryIds" class="col-md-2 control-label">intrări</label>
              <div class="col-md-10">
                <select id="entryIds" name="entryIds[]" class="form-control" multiple>
                  {foreach $entryIds as $eid}
                    <option value="{$eid}" selected></option>
                  {/foreach}
                </select>
              </div>
            </div>
            
          </div>

          <div class="col-md-6 form-horizontal">

            {include "bits/fhf.tpl"
            field="hyphenations"
            value=$lexeme->hyphenations
            label="silabisiri"
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

      <div class="panel-body">

        <div class="row">
          <div class="col-md-6 form-horizontal">

            {assign var="readonly" value=!$canEdit.loc && $lexeme->isLoc}

            <div class="form-group">
              <label class="col-md-3 control-label">lexem compus</label>

              <div class="col-md-9">
                <div class="checkbox">
                  <label>
                    <input type="checkbox"
                           name="compound"
                           value="1"
                           {if $lexeme->compound}checked{/if}
                           {if $readonly}disabled{/if}
                           >
                  </label>
                </div>
              </div>
            </div>

            {* Fields for simple lexemes *}
            <div id="modelDataSimple" {if $lexeme->compound}style="display: none"{/if}>
              <div class="form-group">
                <label class="col-md-3 control-label">tip + număr</label>

                <div class="col-md-9 form-inline" data-model-dropdown>
                  <input type="hidden" name="locVersion" value="6.0" data-loc-version>

                  <select name="modelType"
                          class="form-control"
                          {if $readonly}disabled{/if}
                          data-model-type
                          data-selected="{$lexeme->modelType}">
                  </select>

                  <select name="modelNumber"
                          class="form-control"
                          {if $readonly}disabled{/if}
                          data-model-number
                          data-selected="{$lexeme->modelNumber}">
                  </select>
                  
                  <input type="text"
                         class="form-control"
                         name="restriction"
                         value="{$lexeme->restriction}"
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
            </div>

            {* Fields for compound lexemes *}
            <div id="modelDataCompound" {if !$lexeme->compound}style="display: none"{/if}>

              <div class="form-group">
                <label class="col-md-3 control-label">tip</label>

                <div class="col-md-9 form-inline">
                  <select name="compoundModelType" class="form-control">
                    {foreach $modelTypes as $mt}
                      <option value="{$mt->code}"
                              {if $lexeme->modelType == $mt->code}selected{/if}>
                        {$mt->code}
                      </option>
                    {/foreach}
                  </select>

                  <input type="text"
                         class="form-control"
                         name="compoundRestriction"
                         value="{$lexeme->restriction}"
                         size="5"
                         placeholder="restricții"
                         {if $readonly}readonly{/if}>
                </div>
              </div>

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

            <div class="form-group">
              <div class="col-md-offset-3 col-md-9">
                <div class="checkbox">
                  <label>
                    <input type="checkbox"
                           name="isLoc"
                           value="1"
                           {if $lexeme->isLoc}checked{/if}
                           {if !$canEdit.loc}disabled{/if}
                           >
                    inclus în LOC
                  </label>
                </div>
              </div>
            </div>

          </div>

          <div class="col-md-6 form-horizontal">

            <div class="form-group">
              <label class="col-md-2 control-label">surse</label>
              <div class="col-md-10">
                <select id="sourceIds"
                        class="form-control""
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
      <div class="panel-heading">Definiții ({$searchResults|count})</div>
      <div class="panel-body panel-admin">
        {foreach $searchResults as $row}
          {include "bits/definition.tpl" showStatus=1}
        {/foreach}
      </div>
    </div>

  </form>
{/block}
