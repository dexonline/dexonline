{extends "layout.tpl"}

{block "title"}
  {cap}{t}word scramble{/t}{/cap}
{/block}

{block "search"}{/block}

{block "content"}
  <div id="mainMenu" class="panel panel-default">
    <div class="panel-heading">
      {cap}{t}word scramble{/t}{/cap}
    </div>

    <div class="panel-body">
      <form class="form-horizontal">

        <div>
          <button id="startGameButton" class="btn btn-success" type="button" disabled>
            <i class="glyphicon glyphicon-play"></i>
            {t}start{/t}
          </button>
          <button id="optionsButton"
                  class="btn btn-default"
                  type="button"
                  data-toggle="collapse"
                  data-target="#optionsDiv">
            <i class="glyphicon glyphicon-wrench"></i>
            {t}options{/t}
          </button>
        </div>

        <div id="optionsDiv" class="voffset3 collapse">

          <div class="form-group">
            <label class="col-sm-2 control-label">{t}object{/t}</label>
            <div class="col-sm-10">
              <div class="btn-group" data-toggle="buttons">
                <label class="btn btn-info active">
                  <input type="radio" name="mode" value="0"> {t}all words{/t}
                </label>
                <label class="btn btn-info">
                  <input type="radio" name="mode" value="1"> {t}one anagram{/t}
                </label>
                <label class="btn btn-info">
                  <input type="radio" name="mode" value="2"> {t}all anagrams{/t}
                </label>
              </div>
            </div>
          </div>

          <div class="form-group">
            <label class="col-sm-2 control-label">nivel</label>
            <div class="col-sm-10">
              <div class="btn-group" data-toggle="buttons">
                <label class="btn btn-info">
                  <input type="radio" name="level" value="0"> 4 {t}letters{/t}
                </label>
                <label class="btn btn-info active">
                  <input type="radio" name="level" value="1"> 5 {t}letters{/t}
                </label>
                <label class="btn btn-info">
                  <input type="radio" name="level" value="2"> 6 {t}letters{/t}
                </label>
                <label class="btn btn-info">
                  <input type="radio" name="level" value="3"> 7 {t}letters{/t}
                </label>
              </div>
            </div>
          </div>

          <div class="form-group">
            <label class="col-sm-2 control-label">{t}diacritics{/t}</label>
            <div class="col-sm-10">
              <div class="btn-group" data-toggle="buttons">
                <label class="btn btn-info active">
                  <input type="radio" name="useDiacritics" value="0"> {t}no{/t}
                </label>
                <label class="btn btn-info">
                  <input type="radio" name="useDiacritics" value="1"> {t}yes{/t}
                </label>
              </div>
            </div>
          </div>

          <div class="form-group">
            <label class="col-sm-2 control-label">{t}time{/t}</label>
            <div class="col-sm-10">
              <div class="btn-group" data-toggle="buttons">
                {for $time=1 to 5}
                  <label class="btn btn-info {if $time == 3}active{/if}">
                    <input type="radio" name="seconds" value="{$time-1}">
                    {t count=$time 1=$time plural="%1 minutes"}one minute{/t}
                  </label>
                {/for}
              </div>
            </div>
          </div>
        </div>

        <h3>{t}Object of the game{/t}</h3>
        <p>
          {t}Find as many words as you can using the given letters, before the
          time runs out.{/t}
        </p>

        <p>
          {t}There are three game types:{/t}
        </p>

        <ol>
          <li>
            <b>{cap}{t}all words{/t}{/cap}:</b>
            {t}find all words having at least three letters.{/t}
          </li>
          <li>
            <b>{cap}{t}one anagram{/t}{/cap}:</b>
            {t}find a word using all the letters, then receive a new set of letters.{/t}
          </li>
          <li>
            <b>{cap}{t}all anagrams{/t}{/cap}:</b>

            {t 1="tanti, titan, țintă"}find all words using all the letters,
            then receive a new set of letters. For example, for the set AINTT
            you must guess all three anagrams (<i>%1</i>) to receive the next
            set.{/t}
          </li>
        </ol>

        <h3>{t}Instructions{/t}</h3>
        <p>
          {t}When using a keyboard, simply type the letters. When using a
          mouse, click on the letters, then click on the magnifier to check a
          word.{/t}
        </p>

        <p>
          {t 1="ĂÂÎȘȚ" 2="AIST"}Legal words include conjugations (such as <i>lucrez</i>)
          and declensions (such as <i>verzi</i>). If you opted for diacritics,
          then the letters %1 cannot be interchanged with %2.{/t}
        </p>

        <p>
          {t}Special keys{/t}:
        </p>

        <ul>
          <li><i>Enter</i> {t}checks the word{/t};</li>
          <li><i>Backspace</i> {t}deletes the last letter{/t};</li>
          <li><i>Escape</i> {t}deletes all the letters{/t};</li>
          <li><i>slash (/)</i> {t}shuffles the letters{/t}.</li>
        </ul>

        <p>
          {t}Below the game area there is{/t}:
        </p>

        <ul>
          <li>
            <i class="glyphicon glyphicon-hourglass text-big vcenter"></i>
            {t}time remaining{/t};
          </li>
          <li>
            <i class="glyphicon glyphicon-eye-open text-big vcenter"></i>
            {t}number of words found / total number of words{/t};
          </li>
          <li>
            <i class="glyphicon glyphicon-piggy-bank text-big vcenter"></i>
            {t}score (longer words earn more points){/t}.
          </li>
        </ul>

      </form>
    </div>
  </div>

  <div id="gamePanel" class="panel panel-default">
    <div class="panel-heading">
      {cap}{t}word scramble{/t}{/cap}
    </div>

    <div class="panel-body">
      <div id="canvasWrap">
      </div>

      <div id="gameStats">
        <div class="pull-left">
          <i class="glyphicon glyphicon-hourglass"></i>
          <span id="timer"></span>
        </div>

        <span id="wordCountDiv">
          <i class="glyphicon glyphicon-eye-open"></i>
          <span id="foundWords"></span> /
          <span id="maxWords"></span>
        </span>

        <div class="pull-right">
          <i class="glyphicon glyphicon-piggy-bank"></i>
          <span id="score"></span>
        </div>
      </div>

      <div class="text-center" style="clear: both">
        <button id="restartGameButton" class="btn btn-success" type="button">
          <i class="glyphicon glyphicon-repeat"></i>
          {t}play again{/t}
        </button>
      </div>
    </div>
  </div>

  <div id="wordListPanel" class="panel panel-default">
    <div class="panel-heading">
      {t}Legal words{/t}
    </div>

    <div class="panel-body">
      <div id="legalWords" class="row">
        <div id="wordStem" class="col-xs-6 col-sm-3 col-md-2">
          <a href="definitie/" target="_blank" class="text-danger">
            <i class="glyphicon glyphicon-remove"></i>
            <span></span>
          </a>
        </div>
      </div>
    </div>
  </div>

  <div id="gameIdPanel" class="panel panel-default">
    <div class="panel-body">
      Permalink:
      <a id="permalink" href="#">
        <i class="glyphicon glyphicon-link"></i>
        <span></span>
      </a>

      <p class="help-block">
        {t}You can share this link with your friends to compare scores on the
        same letter sets.{/t}
      </p>
    </div>
  </div>
{/block}
