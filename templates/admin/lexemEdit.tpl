{extends file="admin/layout.tpl"}

{block name=title}Editare lexem: {$lexem->form}{/block}

{block name=adminHeader}{/block}

{block name=content}
  {include file="bits/phpConstants.tpl"}

  {assign var="searchResults" value=$searchResults|default:null}

  <script>
   sourceMap = JSON.parse('{$jsonSources}');
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
    <input type="hidden" name="lexemId" value="{$lexem->id}">
    <input type="hidden" name="jsonMeanings" value="">
    <input type="hidden" name="mergeLexemId" value="">

    {include file="admin/lexemEditActions.tpl"}

    <div id="wmCanvas"></div>

    <div class="box" data-id="properties" data-title="Proprietăți" data-left="0" data-top="0" data-width="330" data-height="320">
      <table>
        <tr>
          <td><label for="lexemForm">nume:</label></td>
          <td>
            <input type="text" id="lexemForm" name="lexemForm" value="{$lexem->form|escape}" size="20" {if !$canEdit.form}readonly{/if}>
            
            <span class="tooltip2" title="Cuvântul-titlu. Accentul trebuie indicat chiar și pentru lexemele monosilabice, altfel paradigma nu va
                                          conține deloc accente. Valoarea acestui câmp este folosită la căutări și este vizibilă public la afișarea flexiunilor unui cuvânt. Odată
                                          ce un lexem a fost inclus în LOC, numele și descrierea lexemului mai pot fi modificate numai de către moderatorii LOC.">&nbsp;</span>
          </td>
        </tr>

        <tr>
          <td><label for="lexemNumber">număr:</label></td>
          <td>
            <input type="number" id="lexemNumber" name="lexemNumber" value="{$lexem->number}"
                   min="1" max="99" maxlength="2" size="2" {if !$canEdit.general}readonly{/if}>
            
            <span class="tooltip2" title="Opțional, pentru numerotarea omonimelor.">&nbsp;</span>
          </td>
        </tr>
        
        <tr>
          <td><label for="lexemDescription">descriere:</label></td>
          <td>
            <input type="text" id="lexemDescription" name="lexemDescription" value="{$lexem->description|escape}" size="20"
                   placeholder="opțională, pentru diferențierea omonimelor" {if !$canEdit.description}readonly{/if}>
            <span class="tooltip2" title="O scurtă descriere, vizibilă public, pentru diferențierea omonimelor.">&nbsp;</span>
          </td>
        </tr>
        
        <tr>  
          <td><label for="structStatus">structurare:</label></td>
          <td>
            {include file="bits/structStatus.tpl" selected=$lexem->structStatus canEdit=$canEdit.structStatus}

            <span class="tooltip2" title="Cât timp structurarea este „în lucru”, persoanele autorizate pot modifica sensurile, variantele, silabisirile
                                          și pronunțiile. După trecerea în starea „așteaptă moderarea”, doar moderatorii mai pot schimba aceste valori.">&nbsp;</span>
          </td>
        </tr>
        
        <tr>  
          <td><label for="structuristId">structurist:</label></td>
          <td>
            <input id="structuristId" name="structuristId" value="{$lexem->structuristId}" type="text">

            <span class="tooltip2"
                  title="Structuristul este implicit utilizatorul care marchează structurarea ca „în lucru”.
                         Doar administratorii îl pot modifica, iar structuristul însuși se poate dezabona (șterge).">&nbsp;</span>
          </td>
        </tr>
        
        <tr>
          <td><label for="hyphenations">silabisiri:</label></td>
          <td>
            <input id="hyphenations" name="hyphenations" type="text" value="{$lexem->hyphenations}" size="20"
                   placeholder="opționale, despărțite prin virgule" {if !$canEdit.hyphenations}readonly{/if}>
          </td>
        </tr>
        
        <tr>
          <td><label for="pronunciations">pronunții:</label></td>
          <td>
            <input id="pronunciations" name="pronunciations" type="text" value="{$lexem->pronunciations}" size="20"
                   placeholder="opționale, despărțite prin virgule" {if !$canEdit.pronunciations}readonly{/if}>
          </td>
        </tr>
        
        <tr>
          <td><label for="needsAccent">necesită accent:</label></td>
          <td>        
            <input type="checkbox" id="needsAccent" name="needsAccent" value="1" {if !$lexem->noAccent}checked{/if}>
            <span class="tooltip2" title="Majoritatea lexemelor necesită accent. Excepție fac cuvintele compuse, denumirile științifice de animale și
                                          plante, elementele de compunere etc.">&nbsp;</span>
          </td>
        </tr>
        
        <tr>
          <td><label for="variantOfId">variantă a lui:</label></td>
          <td>
            <input id="variantOfId" name="variantOfId" value="{$lexem->variantOfId}" type="text" {if !$canEdit.variants}readonly{/if}>
            <span class="tooltip2"
                  title="Variantele nu pot avea sensuri, exemple, variante sau etimologii proprii. Ele pot avea pronunții și silabisiri proprii.">&nbsp;</span>
          </td>
        </tr>
        
        <tr>  
          <td><label for="variantIds">variante:</label></td>
          <td>
            <input id="variantIds" name="variantIds" value="{','|implode:$variantIds}" type="text" {if !$canEdit.variants}readonly{/if}>
            <span class="tooltip2"
                  title="Variantele nu pot avea sensuri, exemple, variante sau etimologii proprii. Ele pot avea pronunții și silabisiri proprii.">&nbsp;</span>
          </td>
        </tr>
        
        {if $homonyms}
          <tr>
            <td>omonime:</td>
            <td>
              {foreach from=$homonyms item=h}
                {assign var=lms value=$h->getLexemModels()}
                {include file="bits/lexemLink.tpl" lexem=$h}
                {$lms[0]->modelType}{$lms[0]->modelNumber}{$lms[0]->restriction}<br>
              {/foreach}
            </td>
          </tr>
        {/if}
      </table>
    </div>

    <div class="box" data-id="paradigm" data-title="Paradigmă" data-left="345" data-top="0" data-width="650" data-height="320">
      {include file="bits/lexemEditModel.tpl"
      id="stem"
      lm=$stemLexemModel
      models=$modelsT}

      <div>
        <div id="paradigmTabs">
          {if $canEdit.paradigm}
            <a href="#" id="addLexemModel">adaugă un model</a>
          {/if}
          <ul>
            {foreach from=$lexemModels key=i item=lm}
              <li>
                {if $canEdit.paradigm}
                  <span class="ui-icon ui-icon-arrow-4"></span>
                {/if}
                <a href="#lmTab_{$i}">{$lm->modelType}{$lm->modelNumber}</a>
                {if $canEdit.loc || !$lm->isLoc}
                  <span class="ui-icon ui-icon-close"></span>
                {/if}
              </li>
            {/foreach}
          </ul>

          {foreach from=$lexemModels key=i item=lm}
            {include file="bits/lexemEditModel.tpl"
            id=$i
            lm=$lm
            models=$models[$i]}
          {/foreach}
        </div>
        <br>

        Comentarii despre paradigmă:
        <br>
        
        <textarea name="lexemComment" rows="3" cols="60" class="commentTextArea"
                  placeholder="Dacă observați greșeli în paradigmă, notați-le în acest câmp și ele vor fi semnalate unui moderator cu drept de gestiune a LOC."
                  >{$lexem->comment|escape}</textarea>
      </div>
    </div>

    <div class="box meaningTreeContainer" data-id="meaningTree" data-title="Sensuri" data-left="10" data-top="330" data-width="960" data-height="280" data-minimized="1">
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
        {else}
          <span class="tooltip2" title="Sensurile, variantele, pronunțiile și silabisirile pot fi modificate doar cât timp structurarea este „în lucru”.">&nbsp;</span>
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
            <label for="editorSources">surse:</label></td>
          <select id="editorSources" multiple="multiple">
            {foreach from=$sources item=s}
              <option value="{$s->id}">{$s->shortName}</option>
            {/foreach}
          </select>
          </div>

          <div>
            <label for="editorTags">etichete:</label>
            <select id="editorTags" multiple="multiple">
              {foreach from=$tags item=mt}
                <option value="{$mt->id}">{$mt->value}</option>
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
              <input class="editorRelation" data-placeholder="adaugă sinonime..." type="hidden">
            </span>
            <span class="relationWrapper" data-type="2">
              <input class="editorRelation" data-placeholder="adaugă antonime..." type="hidden">
            </span>
            <span class="relationWrapper" data-type="3">
              <input class="editorRelation" data-placeholder="adaugă diminutive..." type="hidden">
            </span>
            <span class="relationWrapper" data-type="4">
              <input class="editorRelation" data-placeholder="adaugă augmentative..." type="hidden">
            </span>
          </div>

          <input id="editMeaningAcceptButton" type="button" disabled value="acceptă">
          <input id="editMeaningCancelButton" type="button" disabled value="renunță">
        </div>
      </div>
    {/if}

    <div class="box" data-id="definitions" data-title="Definiții asociate ({$searchResults|@count})" data-left="0" data-top="335" data-width="995" data-height="300">
      <div>
        <select id="defFilterSelect">
          <option value="">toate</option>
          <option value="structured">structurate</option>
          <option value="unstructured">nestructurate</option>
        </select>
        <select class="toggleRepSelect" data-order="1">
          <option value="0">text</option>
          <option value="1" selected>html</option>
        </select>
        <select class="toggleRepSelect" data-order="2">
          <option value="0">expandat</option>
          <option value="1" selected>abreviat</option>
        </select>
      </div>

      {foreach from=$searchResults item=row}
        {$def=$row->definition}
        <div class="defWrapper {if $def->structured}structured{else}unstructured{/if}" id="def_{$def->id}">
          <div data-code="0" class="rep internal hidden">{$def->internalRepAbbrev|escape}</div>
          <div data-code="1" class="rep hidden">{$def->htmlRepAbbrev}</div>
          <div data-code="2" class="rep internal hidden">{$def->internalRep|escape}</div>
          <div data-code="3" data-active class="rep">{$def->htmlRep}</div>
          <span class="defDetails">
            id: {$def->id}
            | sursa: {$row->source->shortName|escape}
            | starea: {$def->getStatusName()}
            {if $canEdit.general}
              | <a href="definitionEdit.php?definitionId={$def->id}" target="_blank">editează</a>
              | <a href="lexemEdit.php?lexemId={$lexem->id}&amp;dissociateDefinitionId={$def->id}"
                   title="disociază definiția de lexem" onclick="return confirmDissociateDefinition({$def->id})">disociază</a>
            {/if}
            | <a href="#" class="toggleRepLink" title="comută între notația internă și HTML"
                 data-value="1" data-order="1" data-other-text="html">text</a>
            | <a href="#" class="toggleRepLink" title="contractează sau expandează abrevierile"
                 data-value="1" data-order="2" data-other-text="abreviat">expandat</a>
            {if $canEdit.defStructured}
              | <a href="#" class="toggleStructuredLink" title="comută definiția între structurată și nestructurată"
                   >{if $def->structured}structurată{else}nestructurată{/if}</a>
            {/if}
          </span>
          {if $row->comment}
            <div class="commentInternalRep">
              Comentariu: {$row->comment->contents} -
              <a href="{$wwwRoot}utilizator/{$row->commentAuthor->nick|escape:"url"}">{$row->commentAuthor->nick|escape}</a>
            </div>
          {/if}
        </div>
      {/foreach}

      {if $canEdit.general}
        <div class="addDefinition">
          <input type="text" id="associateDefinitionId" name="associateDefinitionId">
          <input type="submit" name="associateDefinition" value="Asociază">
        </div>
      {/if}

      {if !count($searchResults) && $canEdit.general}
        <div class="addDefinition">
          Puteți crea o mini-definiție. Introduceți termenul-destinație, fără alte formatări (bold, italic etc.):<br>
          
          <b>{$definitionLexem|escape}</b> v. <input type="text" name="miniDefTarget" size="20" class="miniDefTarget">.
          &nbsp;&nbsp;
          <input type="submit" name="createDefinition" value="Creează">
        </div>
      {/if}
    </div>
  </form>

  <script>
   $(lexemEditInit);
  </script>
{/block}
