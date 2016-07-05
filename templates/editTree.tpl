{extends file="layout.tpl"}

{block name=title}
  {if $t->id}
    Arbore {$t->description}
  {else}
    Arbore nou
  {/if}
{/block}

{block name=content}
  <h3>
    {if $t->id}
      Editează arborele
    {else}
      Adaugă un arbore
    {/if}
  </h3>

  <form action="editTree.php" method="post" role="form">
    <input type="hidden" name="id" value="{$t->id}">

    {include "bits/fgf.tpl" field="description" value=$t->description label="descriere"}

    <div class="form-group"">
      <label for="entryIds">intrări</label>
      <select id="entryIds" name="entryIds[]" style="width: 100%" multiple>
        {foreach $entryIds as $e}
          <option value="{$e}" selected></option>
        {/foreach}
      </select>
    </div>

    <button type="submit" class="btn btn-primary" name="save">
      <i class="glyphicon glyphicon-floppy-disk"></i>
      salvează
    </button>

    <a href="{if $t->id}?id={$t->id}{/if}">
      anulează
    </a>

    <button type="submit" class="btn btn-danger pull-right" name="delete">
      <i class="glyphicon glyphicon-trash"></i>
      șterge
    </button>
  </form>
{/block}
