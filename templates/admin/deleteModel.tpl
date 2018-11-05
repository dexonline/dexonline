{extends "layout-admin.tpl"}

{block "title"}Ștergere model{/block}

{block "content"}
  <h3>Ștergere model {$modelType}{$modelNumber}</h3>

  <form method="post">
    <input type="hidden" name="modelType" value="{$modelType}">
    <input type="hidden" name="modelNumber" value="{$modelNumber}">

    {if count($lexemes)}
      <div class="alert alert-warning alert-dismissible" role="alert">
        <button type="button" class="close" data-dismiss="alert">
          <span aria-hidden="true">&times;</span>
        </button>
        Există {$lexemes|@count} lexem(e) etichetate cu acest model. Dacă
        apăsați butonul "Confirmă", ele vor fi reetichetate cu modelul T1.
      </div>
    {else}
      <p>
        Nu există lexeme etichetate cu acest model. Modelul poate fi șters
        fără probleme.
      </p>
    {/if}

    {foreach $lexemes as $l}
      {include "bits/lexemeName.tpl" lexeme=$l}
      <small class="text-muted">({$l->modelType}{$l->modelNumber})</small>
      &nbsp;&nbsp;
      <a href="../admin/lexemeEdit.php?lexemeId={$l->id}">editează</a>
      <br>
    {/foreach}
    <br>

    <button type="submit" class="btn btn-danger" name="deleteButton">
      <i class="glyphicon glyphicon-trash"></i>
      șterge
    </button>
  </form>
{/block}
