{extends "layout.tpl"}

{block "title"}{'word scramble'|_|capitalize}{/block}

{block "search"}{/block}

{block "content"}
  <div id="mainMenu" class="panel panel-default">
    <div class="panel-heading">
      {'word scramble'|_|capitalize}
    </div>

    <div class="panel-body">
      <form class="form-horizontal">

        <div>
          <button id="startGameButton" class="btn btn-success" type="button" disabled>
            <i class="glyphicon glyphicon-play"></i>
            {'start'|_}
          </button>
          <button id="optionsButton"
                  class="btn btn-default"
                  type="button"
                  data-toggle="collapse"
                  data-target="#optionsDiv">
            <i class="glyphicon glyphicon-wrench"></i>
            {'options'|_}
          </button>
        </div>

        <div id="optionsDiv" class="voffset3 collapse">

          <div class="form-group">
            <label class="col-sm-2 control-label">{'object'|_}</label>
            <div class="col-sm-10">
              <div class="btn-group" data-toggle="buttons">
                <label class="btn btn-info active">
                  <input type="radio" name="mode" value="0"> {'all words'|_}
                </label>
                <label class="btn btn-info">
                  <input type="radio" name="mode" value="1"> {'one anagram'|_}
                </label>
                <label class="btn btn-info">
                  <input type="radio" name="mode" value="2"> {'all anagrams'|_}
                </label>
              </div>
            </div>
          </div>

          <div class="form-group">
            <label class="col-sm-2 control-label">nivel</label>
            <div class="col-sm-10">
              <div class="btn-group" data-toggle="buttons">
                <label class="btn btn-info">
                  <input type="radio" name="level" value="0"> 4 {'letters'|_}
                </label>
                <label class="btn btn-info active">
                  <input type="radio" name="level" value="1"> 5 {'letters'|_}
                </label>
                <label class="btn btn-info">
                  <input type="radio" name="level" value="2"> 6 {'letters'|_}
                </label>
                <label class="btn btn-info">
                  <input type="radio" name="level" value="3"> 7 {'letters'|_}
                </label>
              </div>
            </div>
          </div>

          <div class="form-group">
            <label class="col-sm-2 control-label">{'diacritics'|_}</label>
            <div class="col-sm-10">
              <div class="btn-group" data-toggle="buttons">
                <label class="btn btn-info active">
                  <input type="radio" name="useDiacritics" value="0"> {'no'|_}
                </label>
                <label class="btn btn-info">
                  <input type="radio" name="useDiacritics" value="1"> {'yes'|_}
                </label>
              </div>
            </div>
          </div>

          <div class="form-group">
            <label class="col-sm-2 control-label">{'time'|_}</label>
            <div class="col-sm-10">
              <div class="btn-group" data-toggle="buttons">
                <label class="btn btn-info">
                  <input type="radio" name="seconds" value="0"> {'one minute'|_}
                </label>
                <label class="btn btn-info">
                  <input type="radio" name="seconds" value="1"> 2 {'minutes'|_}
                </label>
                <label class="btn btn-info active">
                  <input type="radio" name="seconds" value="2"> 3 {'minutes'|_}
                </label>
                <label class="btn btn-info">
                  <input type="radio" name="seconds" value="3"> 4 {'minutes'|_}
                </label>
                <label class="btn btn-info">
                  <input type="radio" name="seconds" value="4"> 5 {'minutes'|_}
                </label>
              </div>
            </div>
          </div>
        </div>

        <h3>{'Object of the game'|_}</h3>
        <p>
          {'Find as many words as you can using the given letters, before the
          time runs out.'|_}
        </p>

        <p>
          {'There are three game types:'|_}
        </p>

        <ol>
          <li>
            <b>{'all words'|_|capitalize}:</b>
            {'find all words having at least three letters.'|_}
          </li>
          <li>
            <b>{'one anagram'|_|capitalize}:</b>
            {'find a word using all the letters, then receive a new set of
            letters.'|_}
          </li>
          <li>
            <b>{'all anagrams'|_|capitalize}:</b>
            {'find all words using all the letters, then receive a new set of
            letters. For example, for the set AINTT you must guess all three
            anagrams (<i>%s</i>) to receive the next set.'|_|sprintf:"tanti, titan, țintă"}
          </li>
        </ol>

        <h3>Instructions</h3>
        <p>
          {'When using a keyboard, simply type the letters. When using a
          mouse, click on the letters, then click on the magnifier to check a
          word.'|_}
        </p>

        <p>
          {'Legal words include conjugations (such as <i>lucrez</i>) and declensions
          (such as <i>verzi</i>). If you opted for diacritics, then the
          letters %s cannot be interchanged with %s.'|_|sprintf:"ĂÂÎȘȚ":"AIST"}
        </p>

        <p>
          {'Special keys'|_}:
        </p>

        <ul>
          <li><i>Enter</i> {'checks the word'|_};</li>
          <li><i>Backspace</i> {'deletes the last letter'|_};</li>
          <li><i>Escape</i> {'deletes all the letters'|_};</li>
          <li><i>slash (/)</i> {'shuffles the letters'|_}.</li>
        </ul>

        <p>
          {'Below the game area there is'|_}:
        </p>

        <ul>
          <li>
            <i class="glyphicon glyphicon-hourglass text-big vcenter"></i>
            {'time remaining'|_};
          </li>
          <li>
            <i class="glyphicon glyphicon-eye-open text-big vcenter"></i>
            {'number of words found / total number of words'|_};
          </li>
          <li>
            <i class="glyphicon glyphicon-piggy-bank text-big vcenter"></i>
            {'score (longer words earn more points)'|_}.
          </li>
        </ul>

      </form>
    </div>
  </div>

  <div id="gamePanel" class="panel panel-default">
    <div class="panel-heading">
      {'word scramble'|_|capitalize}
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
          {'play again'|_}
        </button>
      </div>
    </div>
  </div>

  <div id="wordListPanel" class="panel panel-default">
    <div class="panel-heading">
      {'Legal words'|_}
    </div>

    <div class="panel-body">
      <div id="legalWords" class="row">
        <div id="wordStem" class="col-xs-6 col-sm-3 col-md-2">
          <a href="{$wwwRoot}definitie/" target="_blank" class="text-danger">
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
        {'You can share this link with your friends to compare scores on the
        same letter sets.'|_}
      </p>
    </div>
  </div>
{/block}
