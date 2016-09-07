{extends file="layout-admin.tpl"}

{block name=title}Unificare lexeme{/block}

{block name=content}
  <h3>Unificare lexeme - {$lexems|@count} rezultate</h3>

  <div class="alert alert-info alert-dismissible" role="alert">
    <button type="button" class="close" data-dismiss="alert">
      <span aria-hidden="true">&times;</span>
    </button>

    Pentru fiecare lexem la plural sunt indicate lexemele la singular
    corespunzătoare. Bifați unul sau mai multe, după caz. Puteți salva
    pagina în orice moment; lexemele rămase vor fi afișate din nou la
    reîncărcarea paginii. Pentru moment, nu există o modalitate de a
    „ignora” un lexem. Lexemele pe care nu le unificați vor apărea mereu
    în listă.
  </div>

  <form class="form-inline">
    <div class="form-group">
      <label>tipul lexemului</label>
      <select name="modelType" class="form-control">
        <option value="">Toate (lent)</option>
        <option value="M" {if $modelType == 'M'}selected="selected"{/if}>M</option>
        <option value="F" {if $modelType == 'F'}selected="selected"{/if}>F</option>
        <option value="N" {if $modelType == 'N'}selected="selected"{/if}>N</option>
        <option value="T" {if $modelType == 'T'}selected="selected"{/if}>T (lent)</option>
      </select>
    </div>
    <button type="submit" class="btn btn-primary">
      filtrează
    </button>
  </form>

  <form class="voffset4" method="post">
    <input type="hidden" name="modelType" value="{$modelType}"/>

    {foreach from=$lexems item=l key=lIter}
      <div class="blLexem">
        <div class="blLexemTitle">
          <span class="name">{$lIter+1}. {$l->form|escape}</span>
          {$l->modelType}{$l->modelNumber}{$l->restriction}
          <span class="{if $l->isLoc}isLoc{else}isNotLoc{/if}">LOC</span>
          {*
             {assign var="ifs" value=$l->loadInflectedForms()}
             {foreach from=$ifs item=if}
             {$if->form}
             {/foreach}
           *}
          {strip}
          <a class="noBorder" target="_blank" href="../admin/lexemEdit.php?lexemId={$l->id}">
            <i class="glyphicon glyphicon-pencil" title="editează lexemul"></i>
          </a> &nbsp;
          <a class="noBorder" href="#" onclick="return mlUpdateDefVisibility({$l->id}, 'def_{$l->id}')">
            <i class="glyphicon glyphicon-folder-open" title="arată definițiile"></i>
          </a>
        {/strip}
        </div>
        <div id="def_{$l->id}" style="display:none"></div>
        <div class="blLexemBody">
          {foreach from=$l->matches item=match}
            {assign var="checkboxId" value="merge_`$l->id`_`$match->id`"}
            <input type="checkbox" id="{$checkboxId}" name="{$checkboxId}" value="1"/>
            <label for="{$checkboxId}"> 
              Unifică cu {$match->form} {$match->modelType}{$match->modelNumber}{$match->restriction}
            </label>
            <span class="{if $match->isLoc}isLoc{else}isNotLoc{/if}">LOC</span>
            {strip}
            <a class="noBorder" target="_blank" href="../admin/lexemEdit.php?lexemId={$match->id}">
              <i class="glyphicon glyphicon-pencil" title="editează lexemul"></i>
            </a> &nbsp;
            <a class="noBorder" href="#" onclick="return mlUpdateDefVisibility({$match->id}, 'def_{$match->id}')">
              <i class="glyphicon glyphicon-folder-open" title="arată definițiile"></i>
            </a>
            {/strip}
            <br/>
            {if ($l->isLoc && !$match->isLoc) || $match->lostForms}
              <ul class="mlNotes">
                {if ($l->isLoc && !$match->isLoc)}
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
            <div id="def_{$match->id}" style="display:none"></div>
          {/foreach}
        </div>
      </div>
    {/foreach}

    <input type="submit" name="submitButton" value="Salvează"/>
  </form>
{/block}
