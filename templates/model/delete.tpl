{extends "layout-admin.tpl"}

{block "title"}Ștergere model{/block}

{block "content"}
  <h3>Ștergere model {$modelType}{$modelNumber}</h3>

  <form method="post">
    <input type="hidden" name="modelType" value="{$modelType}">
    <input type="hidden" name="modelNumber" value="{$modelNumber}">

    {if count($lexemes)}
      {notice icon="warning"}
        Există {$lexemes|@count} lexem(e) etichetate cu acest model. Dacă
        apăsați butonul „șterge”, ele vor fi reetichetate cu modelul T1.
      {/notice}
    {else}
      <p>
        Nu există lexeme etichetate cu acest model. Modelul poate fi șters
        fără probleme.
      </p>
    {/if}

    {foreach $lexemes as $l}
      <div>
        {include "bits/lexemeName.tpl" lexeme=$l}
        <small class="text-muted me-3">({$l->modelType}{$l->modelNumber})</small>
        <a href="{Router::link('lexeme/edit')}?lexemeId={$l->id}">editează</a>
      </div>
    {/foreach}

    <div class="mt-3">
      <button type="submit" class="btn btn-danger" name="deleteButton">
        {include "bits/icon.tpl" i=delete}
        șterge
      </button>
    </div>
  </form>
{/block}
