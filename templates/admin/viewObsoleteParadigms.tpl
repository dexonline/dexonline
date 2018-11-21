{extends "layout-admin.tpl"}

{block "title"}Lexeme cu paradigme învechite{/block}

{block "content"}

  <h3>{$count} lexeme cu paradigme învechite</h3>

  <p class="help-block">
    Acestea sunt lexeme ale căror modele au fost editate, dar ale căror
    paradigme nu au fost regenerate. Pe vremuri, la editarea modelului, puteam
    regenera doar formele afectate. De cînd cu elidările, această abordare a
    devenit prea complexă. De aceea vă prezentăm această pagină, de unde
    puteți regenera, în timp, paradigmele lexemelor.
  </p>

  {if $count}
    <form class="form-inline">
      <div class="form-group">
        <label for="timer">Regenerează paradigme timp de</label>
        <input type="number" class="form-control" id="timer" name="timer" value="30" size="5">
        <label for="timer">secunde</label>
      </div>
      <button type="submit" class="btn btn-default">
        <i class="glyphicon glyphicon-repeat"></i>
        regenerează
      </button>
    </form>
  {/if}

  {if count($lexemes)}
    <h3>Lista de lexeme (maximum {$smarty.const.MAX_DISPLAYED} afișate)</h3>

    {include "bits/lexemeList.tpl"}
  {/if}

  <hr>
{/block}
