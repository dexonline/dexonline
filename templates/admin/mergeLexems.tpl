{extends file="admin/layout.tpl"}

{block name=title}Unificare lexeme{/block}

{block name=headerTitle}Unificare lexeme{/block}

{block name=content}
  Pentru fiecare lexem la plural sunt indicate lexemele la singular
  corespunzătoare. Bifați unul sau mai multe, după caz. Puteți salva
  pagina în orice moment; lexemele rămase vor fi afișate din nou la
  reîncărcarea paginii. Pentru moment, nu există o modalitate de a
  „ignora” un lexem. Lexemele pe care nu le unificați vor apărea mereu
  în listă.

  <br/><br/>

  <form method="get" action="">
    Filtrează după tipul lexemului:
    <select name="modelType">
      <option value="">Toate (lent)</option>
      <option value="M" {if $modelType == 'M'}selected="selected"{/if}>M</option>
      <option value="F" {if $modelType == 'F'}selected="selected"{/if}>F</option>
      <option value="N" {if $modelType == 'N'}selected="selected"{/if}>N</option>
      <option value="T" {if $modelType == 'T'}selected="selected"{/if}>T (lent)</option>
    </select>
    <input type="submit" value="Filtrează"/>
  </form>

  <h3>{$lexems|@count} rezultate</h3>

  <form action="" method="post">
    <input type="hidden" name="modelType" value="{$modelType}"/>

    {foreach from=$lexems item=l key=lIter}
      {assign var="lms" value=$l->getLexemModels()}
      {assign var="lm" value=$lms[0]}
      <div class="blLexem">
        <div class="blLexemTitle">
          <span class="name">{$lIter+1}. {$l->form|escape}</span>
          {$lm->modelType}{$lm->modelNumber}{$lm->restriction}
          <span class="{if $lm->isLoc}isLoc{else}isNotLoc{/if}">LOC</span>
          {*
             {assign var="ifs" value=$l->loadInflectedForms()}
             {foreach from=$ifs item=if}
             {$if->form}
             {/foreach}
           *}
          {strip}
          <a class="noBorder" target="_blank" href="../admin/lexemEdit.php?lexemId={$l->id}">
            <img src={$imgRoot}/icons/pencil.png alt="editează" title="editează lexemul"/>
          </a> &nbsp;
          <a class="noBorder" href="#" onclick="return mlUpdateDefVisibility({$l->id}, 'def_{$l->id}')">
            <img src={$imgRoot}/icons/book_open.png alt="definiții" title="arată definițiile"/>
          </a>
        {/strip}
        </div>
        <div id="def_{$l->id}" class="blDefinitions" style="display:none"></div>
        <div class="blLexemBody">
          {foreach from=$l->matches item=match}
            {assign var="lmsMatch" value=$match->getLexemModels()}
            {assign var="lmMatch" value=$lmsMatch[0]}
            {assign var="checkboxId" value="merge_`$l->id`_`$match->id`"}
            <input type="checkbox" id="{$checkboxId}" name="{$checkboxId}" value="1"/>
            <label for="{$checkboxId}"> 
              Unifică cu {$match->form} {$lmMatch->modelType}{$lmMatch->modelNumber}{$lmMatch->restriction}
            </label>
            <span class="{if $lmMatch->isLoc}isLoc{else}isNotLoc{/if}">LOC</span>
            {strip}
            <a class="noBorder" target="_blank" href="../admin/lexemEdit.php?lexemId={$match->id}">
              <img src={$imgRoot}/icons/pencil.png alt="editează" title="editează lexemul"/>
            </a> &nbsp;
            <a class="noBorder" href="#" onclick="return mlUpdateDefVisibility({$match->id}, 'def_{$match->id}')">
              <img src={$imgRoot}/icons/book_open.png alt="definiții" title="arată definițiile"/>
            </a>
          {/strip}
          <br/>
          {if ($lm->isLoc && !$lmMatch->isLoc) || $match->lostForms}
            <ul class="mlNotes">
              {if ($lm->isLoc && !$lmMatch->isLoc)}
                <li>Acest lexem va fi adăugat la LOC</li>
              {/if}
              {if $match->addedForms}
                <li>
                  Următoarele forme vor fi adăugate la LOC:
                  {foreach from=$match->addedForms item=form}
                    {$form}
                  {/foreach}
                </li>
              {/if}
              {if $match->lostForms}
                <li>
                  Următoarele forme se vor pierde:
                  {foreach from=$match->lostForms item=form}
                    {$form}
                  {/foreach}
                </li>
              {/if}
            </ul>
          {/if}
          <div id="def_{$match->id}" class="blDefinitions" style="display:none"></div>
          {/foreach}
        </div>
      </div>
    {/foreach}

    <input type="submit" name="submitButton" value="Salvează"/>
  </form>
{/block}
