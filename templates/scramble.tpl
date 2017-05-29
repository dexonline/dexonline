{extends "layout.tpl"}

{block "title"}Omleta Cuvintelor{/block}

{block "search"}{/block}

{block "content"}
  <div id="mainMenu" class="panel panel-default">
    <div class="panel-heading">
      Omleta cuvintelor
    </div>

    <div class="panel-body">
      <form class="form-horizontal">
        <div class="form-group">
          <label class="col-sm-2 control-label">nivel</label>
          <div class="col-sm-10">
            <div class="btn-group" data-toggle="buttons">
              <label class="btn btn-info">
                <input type="radio" name="level" value="4" checked> 4 litere
              </label>
              <label class="btn btn-info active">
                <input type="radio" name="level" value="5"> 5 litere
              </label>
              <label class="btn btn-info">
                <input type="radio" name="level" value="6"> 6 litere
              </label>
              <label class="btn btn-info">
                <input type="radio" name="level" value="7"> 7 litere
              </label>
            </div>
          </div>
        </div>

        <div class="form-group">
          <label class="col-sm-2 control-label">diacritice</label>
          <div class="col-sm-10">
            <div class="btn-group" data-toggle="buttons">
              <label class="btn btn-info active">
                <input type="radio" name="useDiacritics" value="0" checked> nu
              </label>
              <label class="btn btn-info">
                <input type="radio" name="useDiacritics" value="1" checked> da
              </label>
            </div>
          </div>
        </div>

        <div class="form-group">
          <div class="col-sm-offset-2 col-sm-10">
            <button id="startGameButton" class="btn btn-success" type="button">
              <i class="glyphicon glyphicon-play"></i>
              începe
            </button>
          </div>
        </div>
      </form>
    </div>
  </div>

  <div id="gamePanel" class="panel panel-default">
    <div class="panel-heading">
      Omleta cuvintelor
    </div>

    <div class="panel-body">
      <canvas width="480" height="280"></canvas>

      <div id="gameStats">
        <div class="pull-left">
          <i class="glyphicon glyphicon-hourglass"></i>
          <span id="timer">0</span>
        </div>
        <i class="glyphicon glyphicon-piggy-bank"></i>
        <span id="score">0</span>
        <div class="pull-right">
          <i class="glyphicon glyphicon-eye-open"></i>
          <span id="foundWords">0</span> /
          <span id="maxWords">0</span>
        </div>
      </div>
    </div>
  </div>

  <div id="wordListPanel" class="panel panel-default">
    <div class="panel-heading">
      Cuvinte posibile
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
{/block}
