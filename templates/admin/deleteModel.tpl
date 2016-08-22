{extends file="admin/layout.tpl"}

{block name=title}Ștergere model{/block}

{block name=headerTitle}
  Ștergere model {$modelType}{$modelNumber}
{/block}

{block name=content}
  <form action="deleteModel.php" method="post" onsubmit="this.bogusButton.disabled = true;">
    <input type="hidden" name="modelType" value="{$modelType}"/>
    <input type="hidden" name="modelNumber" value="{$modelNumber}"/>

    {if count($lexems)}
      Există {$lexems|@count} lexem(e) etichetate cu acest model. Dacă
      apăsați butonul "Confirmă", ele vor fi reetichetate cu modelul T1.
    {else}
      Nu există lexeme etichetate cu acest model. Modelul poate fi șters
      fără probleme.
    {/if}
    <br/><br/>

    {foreach from=$lexems item=l}
      {include "bits/lexemName.tpl" lexem=$l}
      <span class="deemph">({$l->modelType}{$l->modelNumber})</span>
      &nbsp;&nbsp;
      <a href="../admin/lexemEdit.php?lexemId={$l->id}">editează</a>
      <br/>
    {/foreach}
    <br/>

    <!-- We want to disable the button on click, but still submit a value -->
    <input type="hidden" name="deleteButton" value="1"/>
    {if $locPerm}
      <input type="submit" name="bogusButton" value="Confirmă"/>
    {/if}
  </form>
{/block}
