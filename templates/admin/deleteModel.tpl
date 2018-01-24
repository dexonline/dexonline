{extends "layout-admin.tpl"}

{block "title"}Ștergere model{/block}

{block "content"}
  <h3>Ștergere model {$modelType}{$modelNumber}</h3>

  <form method="post">
    <input type="hidden" name="modelType" value="{$modelType}">
    <input type="hidden" name="modelNumber" value="{$modelNumber}">

    {if count($lexems)}
      <div class="alert alert-warning alert-dismissible" role="alert">
        <button type="button" class="close" data-dismiss="alert">
          <span aria-hidden="true">&times;</span>
        </button>
        Există {$lexems|@count} lexem(e) etichetate cu acest model. Dacă
        apăsați butonul "Confirmă", ele vor fi reetichetate cu modelul T1.
      </div>
    {else}
      <p>
        Nu există lexeme etichetate cu acest model. Modelul poate fi șters
        fără probleme.
      </p>
    {/if}

    {foreach $lexems as $l}
      {include "bits/lexemName.tpl" lexem=$l}
      <small class="text-muted">({$l->modelType}{$l->modelNumber})</small>
      &nbsp;&nbsp;
      <a href="../admin/lexemEdit.php?lexemeId={$l->id}">editează</a>
      <br>
    {/foreach}
    <br>

    <!-- We want to disable the button on click, but still submit a value -->
    {if $locPerm}
      <button type="submit" class="btn btn-danger" name="deleteButton">
        <i class="glyphicon glyphicon-trash"></i>
        șterge
      </button>
    {/if}
  </form>
{/block}
