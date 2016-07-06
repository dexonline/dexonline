{extends file="layout.tpl"}

{block name=title}Editare lexem: {$lexem->form}{/block}

{block name=content}
  <h3>
    Editare lexem: {$lexem->form}
    <span class="pull-right">
      <small><a href="http://wiki.dexonline.ro/wiki/Editarea_lexemelor">instrucțiuni</a></small>
    </span>
  </h3>

  {include file="bits/phpConstants.tpl"}

  {assign var="searchResults" value=$searchResults|default:null}

  <script>
   canEdit = { 'paradigm': {$canEdit.paradigm}, 'loc': {$canEdit.loc} };
  </script>

  {* Stem meaning editor that we clone whenever we append a new meaning *}
  <ul id="stemNode">
    <li>
      <div class="meaningContainer">
        <span class="id"></span>
        <span class="breadcrumb"></span>
        <span class="tags"></span>
        <span class="tagIds"></span>
        <span class="internalRep"></span>
        <span class="htmlRep"></span>
        <span class="internalEtymology"></span>
        <span class="htmlEtymology"></span>
        <span class="internalComment"></span>
        <span class="htmlComment"></span>
        <span class="sources"></span>
        <span class="sourceIds"></span>
        {for $type=1 to Relation::NUM_TYPES}
          <span class="relation" data-type="{$type}"></span>
          <span class="relationIds" data-type="{$type}"></span>
        {/for}
      </div>
    </li>
  </ul>
  
  <form action="lexemEdit.php" method="post">
    <div class="panel panel-default">

      <div class="panel-heading">Proprietăți</div>

      <div class="panel-body">
        <input type="hidden" name="lexemId" value="{$lexem->id}">
        <input type="hidden" name="jsonMeanings" value="">

        <div class="row">
          <div class="col-md-6">

            {include "bits/fgf.tpl"
            field="lexemForm"
            value=$lexem->form
            label="formă"
            readonly=!$canEdit.form}

            {include "bits/fgf.tpl"
            field="lexemNumber"
            type="number"
            value=$lexem->number
            label="număr"
            placeholder="opțional, pentru numerotarea omonimelor"
            readonly=!$canEdit.general}
            
            {include "bits/fgf.tpl"
            field="lexemDescription"
            value=$lexem->description
            label="descriere"
            placeholder="opțională, pentru diferențierea omonimelor"
            readonly=!$canEdit.description}

            {if $homonyms}
              <div class="form-group">
                <label>omonime</label>

                {foreach from=$homonyms item=h}
                  <div>
                    {include file="bits/lexemLink.tpl" lexem=$h}
                    {$h->modelType}{$h->modelNumber}{$h->restriction}
                  </div>
                {/foreach}
              </div>
            {/if}

            <div class="form-group">
              <label for="entryId">intrare</label>
              <select id="entryId" name="entryId">
                {if $lexem->entryId}
                  <option value="{$lexem->entryId}" selected></option>
                {/if}
              </select>
            </div>
            
            <div class="form-group">
              <label for="variantOfId">variantă a lui</label>
              <select id="variantOfId" name="variantOfId" {if !$canEdit.variants}disabled{/if}>
                <option value="{$lexem->variantOfId}" selected></option>
              </select>
            </div>
            
            <div class="form-group">
              <label for="variantIds">variante</label>
              <select id="variantIds" name="variantIds[]" multiple {if !$canEdit.variants}disabled{/if}>
                {foreach $variantIds as $id}
                  <option value="{$id}" selected></option>
                {/foreach}
              </select>
            </div>
            
          </div>

          <div class="col-md-6">

            <div class="form-group">
              <label for="tagIds">etichete</label>
              <select id="tagIds" name="tagIds[]" class="form-control" multiple>
                {foreach $tagIds as $t}
                  <option value="{$t}" selected></option>
                {/foreach}
              </select>
            </div>

            {include "bits/fgf.tpl"
            field="hyphenations"
            value=$lexem->hyphenations
            label="silabisiri"
            placeholder="opționale, despărțite prin virgule"
            readonly=!$canEdit.hyphenations}

            {include "bits/fgf.tpl"
            field="pronunciations"
            value=$lexem->pronunciations
            label="pronunții"
            placeholder="opționale, despărțite prin virgule"
            readonly=!$canEdit.pronunciations}

            <div class="checkbox">
              <label>
                <input type="checkbox" name="needsAccent" value="1" {if !$lexem->noAccent}checked{/if}>
                necesită accent
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

            <div class="form-group {if isset($errors.structStatus)}has-error{/if}">
              <label for="structStatus">structurare</label>
              {include file="bits/structStatus.tpl" selected=$lexem->structStatus canEdit=$canEdit.structStatus}
              {include "bits/fieldErrors.tpl" errors=$errors.structStatus|default:null}
            </div>

            <div class="form-group">
              <label for="structuristId">structurist</label>
              <select id="structuristId" name="structuristId">
                {if $lexem->structuristId}
                  <option value="{$lexem->structuristId}" selected></option>
                {/if}
              </select>
            </div>

          </div>
        </div>
      </div>
    </div>

    <div class="panel panel-default">

      <div class="panel-heading">Model de flexiune</div>

      <div class="panel-body">

        <div class="row">
          <div class="col-md-6">

            {assign var="readonly" value=!$canEdit.loc && $lexem->isLoc}

            <div class="form-group">
              <label>tip + număr + restricții</label>

              <div class="form-inline" data-model-dropdown>
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
                <select class="similarLexem"></select>
              </div>
            {/if}

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

            <div class="form-group">
              <label>surse care atestă flexiunea</label>
              <select id="sourceIds" name="sourceIds[]" multiple {if !$canEdit.sources}disabled{/if}>
                {foreach $lexem->getSourceIds() as $lsId}
                  <option value="{$lsId}" selected></option>
                {/foreach}
              </select>
            </div>
          </div>

          <div class="col-md-6">
            {include "bits/fgf.tpl"
            field="notes"
            value=$lexem->notes
            label="precizări"
            placeholder="explicații despre sursa flexiunii"
            readonly=!$canEdit.tags}

            <div class="form-group">
              <label>comentariu</label>

              <textarea name="lexemComment" class="form-control" rows="4"
                        placeholder="Comentarii și/sau greșeli observate în paradigmă"
                        >{$lexem->comment|escape}</textarea>
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

    {include file="admin/lexemEditActions.tpl"}

    <div class="box meaningTreeContainer" data-id="meaningTree">
      {include file="bits/meaningTree.tpl" meanings=$meanings id="meaningTree"}

      <div id="meaningMenu">
        {if $canEdit.meanings}
          <input type="button" id="addMeaningButton" value="adaugă sens"
                 title="Adaugă un sens ca frate al sensului selectat. Dacă nici un sens nu este selectat, adaugă un sens la sfârșitul listei.">
          <input type="button" id="addSubmeaningButton" value="adaugă subsens" disabled
                 title="Adaugă un sens ca ultimul fiu al sensului selectat">
          <input type="button" id="deleteMeaningButton" value="șterge sens" disabled
                 title="Șterge sensul selectat">
          <input type="button" id="meaningRightButton" class="arrowButton" value="⇨" disabled
                 title="Sensul devine fiu al fratelui său anterior.">
          <input type="button" id="meaningLeftButton" class="arrowButton" value="⇦" disabled
                 title="Sensul devine fratele următor al tatălui său.">
          <input type="button" id="meaningDownButton" class="arrowButton" value="⇩" disabled
                 title="Sensul schimbă locurile cu fratele său următor.">
          <input type="button" id="meaningUpButton" class="arrowButton" value="⇧" disabled
                 title="Sensul schimbă locurile cu fratele său anterior.">
        {/if}
      </div>
    </div>

    {if $canEdit.meanings}
      <div class="box" data-id="meaningEditor" data-title="Editorul de sensuri" data-left="10" data-top="30" data-width="960" data-height="280" data-minimized="1">
        <div id="meaningEditor">
          <textarea id="editorRep" rows="10" cols="10" disabled placeholder="sensul definiției..."></textarea>
          <textarea id="editorEtymology" rows="5" cols="10" disabled placeholder="etimologie..."></textarea>
          <textarea id="editorComment" rows="3" cols="10" disabled placeholder="comentariu..."></textarea>

          <div>
            <label for="editorSources">surse:</label>
            <select id="editorSources" multiple disabled>
              {foreach from=$sources item=s}
                <option value="{$s->id}">{$s->shortName}</option>
              {/foreach}
            </select>
          </div>

          <div>
            <label for="editorTags">etichete:</label>
            <select id="editorTags" multiple disabled>
              {foreach $tags as $t}
                <option value="{$t->id}">{$t->value}</option>
              {/foreach}
            </select>
          </div>

          <div>
            <label for="relationType">relații:</label>
            <select id="relationType" disabled>
              <option value="1" title="sinonime">sinonime</option>
              <option value="2" title="antonime">antonime</option>
              <option value="3" title="diminutive">diminutive</option>
              <option value="4" title="augmentative">augmentative</option>
            </select>
            <span class="relationWrapper" data-type="1">
              <select class="editorRelation" multiple disabled></select>
            </span>
            <span class="relationWrapper" data-type="2">
              <select class="editorRelation" multiple disabled></select>
            </span>
            <span class="relationWrapper" data-type="3">
              <select class="editorRelation" multiple disabled></select>
            </span>
            <span class="relationWrapper" data-type="4">
              <select class="editorRelation" multiple disabled></select>
            </span>
          </div>

          <input id="editMeaningAcceptButton" type="button" disabled value="acceptă">
          <input id="editMeaningCancelButton" type="button" disabled value="renunță">
        </div>
      </div>
    {/if}

  </form>
{/block}
