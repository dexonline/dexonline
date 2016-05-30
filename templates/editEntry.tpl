{extends file="layout.tpl"}

{block name=title}
  {if $e->id}
    Intrare {$e->description}
  {else}
    Intrare nouă
  {/if}
{/block}

{block name=content}
  <h3>
    {if $e->id}
      Editează intrarea
    {else}
      Adaugă o intrare
    {/if}
  </h3>

  <form action="editEntry.php" method="post" role="form">
    <input type="hidden" name="id" value="{$e->id}">

    {include "bits/fgf.tpl" field="description" value=$e->description label="descriere"}

    <div class="form-group"">
      <label for="lexemIds">lexeme</label>
      <select id="lexemIds" name="lexemIds[]" style="width: 100%" multiple>
        {foreach $lexemIds as $l}
          <option value="{$l}" selected></option>
        {/foreach}
      </select>
    </div>

    <button type="submit" class="btn btn-primary" name="save">
      <i class="glyphicon glyphicon-floppy-disk"></i>
      salvează
    </button>

    <a href="{if $e->id}?id={$e->id}{/if}">
      anulează
    </a>

    <button type="submit" class="btn btn-danger pull-right" name="delete">
      <i class="glyphicon glyphicon-trash"></i>
      șterge
    </button>
  </form>
{/block}
