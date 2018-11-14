{extends "layout.tpl"}

{block "title"}{'hangman'|_|capitalize}{/block}

{block "search"}{/block}

{block "content"}
  <script>
   var word = "{$word}";
   var difficulty = "{$difficulty}";
  </script>

  <div class="panel panel-default">
    <div class="panel-heading">
      <h3 class="panel-title">{'hangman'|_|capitalize}</h3>
    </div>
    <div class="panel-body">
      <form id="hangman" action="">

        <div class="graphics">
          <label>{'Lives remaining'|_}: <span id="livesLeft">6</span></label>
          <div class="hangmanPic"> </div>
          <div class="imageLicense">{'images'|_} © dexonline.ro</div>

          <div class="output">
            {section name="ignored" start=0 loop=$wordLength}
              <input style="width:15pt" class="letters" name="out[]" type="text" readonly size="1">
            {/section}
          </div>
        </div>

        <div class="controls">
          {foreach $letters as $letter}
            <input class="letterButtons btn" type="button" value="{$letter|mb_strtoupper}">
          {/foreach}
          <input id="hintButton" type="button" value="{'Get a clue'|_}" class="btn">
        </div>

        <div class="newGameControls">
          <label>Joc nou:</label>
          <button class="btn btn-info" type="button" data-level="1">{'easy'|_}</button>
          <button class="btn btn-info" type="button" data-level="2">{'medium'|_}</button><br>
          <button class="btn btn-info" type="button" data-level="3">{'hard'|_}</button>
          <button class="btn btn-info" type="button" data-level="4">{'expert'|_}</button>
        </div>
      </form>
    </div>
  </div>

  <div id="resultsWrapper" class="panel panel-default">
    <div class="panel-heading">
      <h3 class="panel-title">{'definitions'|_|capitalize}</h3>
    </div>
    <div class="panel-body">
      {foreach $searchResults as $row}
        {include "bits/definition.tpl" showBookmark=1}
      {/foreach}
    </div>
  </div>

  <div id="endModal" class="modal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-body">
          <div class="win text-success">
            {'Congratulations, you win!'|_}
          </div>
          <div class="lose text-danger">
            {'Sorry, you lose.'|_}
          </div>
        </div>
      </div>
    </div>
  </div>

{/block}
