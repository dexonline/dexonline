{extends "layout.tpl"}

{block "title"}{cap}{t}hangman{/t}{/cap}{/block}

{block "search"}{/block}

{block "content"}
  <div class="card mb-3">
    <div class="card-header">
      {cap}{t}hangman{/t}{/cap}
    </div>
    <div class="card-body">
      <form id="hangman" action="">

        <div class="graphics">
          <label>{t}Lives remaining{/t}: <span id="livesLeft"></span></label>
          <div class="hangmanPic"> </div>
          <div class="imageLicense">{t}images{/t} © dexonline.ro</div>

          {* show one input; JS will multiply this once it picks a word *}
          {strip}
          <div class="output">
            <input class="letters" name="out[]" type="text" readonly size="1">
          </div>
          {/strip}
        </div>

        <div class="controls">
          {foreach $letters as $letter}
            <input class="letterButtons btn" type="button" value="{$letter|mb_strtoupper}">
          {/foreach}
          <input id="hintButton" type="button" value="{t}Get a clue{/t}" class="btn">
        </div>

        <div class="newGameControls">
          <label>{t}new game{/t}:</label>
          <button class="btn btn-secondary" type="button" data-level="1">{t}easy{/t}</button>
          <button class="btn btn-secondary" type="button" data-level="2">{t}medium{/t}</button><br>
          <button class="btn btn-secondary" type="button" data-level="3">{t}hard{/t}</button>
          <button class="btn btn-secondary" type="button" data-level="4">{t}expert{/t}</button>
        </div>
      </form>
    </div>
  </div>

  <div id="resultsWrapper" class="card">
    <div class="card-header">
      {cap}{t}definitions{/t}{/cap}
    </div>
    <div class="card-body">
    </div>
  </div>

  <div id="endModal" class="modal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-body">
          <div class="win text-success">
            {t}Congratulations, you win!{/t}
          </div>
          <div class="lose text-danger">
            {t}Sorry, you lose.{/t}
          </div>
        </div>
      </div>
    </div>
  </div>

{/block}
