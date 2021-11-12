{extends "layout-admin.tpl"}

{block "title"}Lexeme cu paradigme învechite{/block}

{block "content"}

  <h3>{$count} lexeme cu paradigme învechite</h3>

  {notice type="info"}
    Acestea sunt lexeme ale căror modele au fost editate, dar ale căror
    paradigme nu au fost regenerate. Pe vremuri, la editarea modelului, puteam
    regenera doar formele afectate. De cînd cu elidările, această abordare a
    devenit prea complexă. De aceea vă prezentăm această pagină, de unde
    puteți regenera, în timp, paradigmele lexemelor.
  {/notice}

  {if $count}
    <form class="row row-cols-sm-auto g-2 mb-3">
      <div class="col-12">
        <label class="col-form-label" for="timer">
          Regenerează paradigme timp de
        </label>
      </div>
      <div class="col-12">
        <input type="number" class="form-control" id="timer" name="timer" value="30" size="5">
      </div>
      <div class="col-12">
        <label class="col-form-label" for="timer">
          secunde
        </label>
      </div>
      <div class="col-12">
        <button type="submit" class="btn btn-primary">
          {include "bits/icon.tpl" i=repeat}
          regenerează
        </button>
      </div>
    </form>
  {/if}

  {if count($lexemes)}
    <h3>Lista de lexeme (maximum {$smarty.const.MAX_DISPLAYED} afișate)</h3>

    {include "bits/lexemeList.tpl"}
  {/if}

  <hr>
{/block}
