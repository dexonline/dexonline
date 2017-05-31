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
          <label class="col-sm-2 control-label">mod</label>
          <div class="col-sm-10">
            <div class="btn-group" data-toggle="buttons">
              <label class="btn btn-info active">
                <input type="radio" name="mode" value="0" checked> toate cuvintele
              </label>
              <label class="btn btn-info">
                <input type="radio" name="mode" value="1" checked> doar anagrame
              </label>
            </div>
            <div class="help-block">
              <b>Toate cuvintele:</b> găsiți toate cuvintele de cel puțin trei litere.
            </div>
            <div class="help-block">
              <b>Doar anagrame:</b> găsiți un cuvânt care folosește
              toate literele, apoi primiți alt set de litere.
            </div>
          </div>
        </div>

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
            <div class="help-block">
              Când alegeți „da”, literele Ă, Â, Î, Ș și Ț au valoare
              proprie și nu pot fi interschimbate cu A, I, S și T.
            </div>
          </div>
        </div>

        <div class="form-group">
          <div class="col-sm-offset-2 col-sm-10">
            <button id="startGameButton" class="btn btn-success" type="button" disabled>
              <i class="glyphicon glyphicon-play"></i>
              începe
            </button>
          </div>
        </div>

        <div class="form-group">
          <label class="col-sm-2 control-label">descriere</label>
          <div class="col-sm-10 checkbox">
            <p>
              Aveți la dispoziție trei minute ca să formați câte cuvinte puteți.
            </p>

            <p>
              Dacă folosiți tastatura, tastați pur și simplu
              literele. Tasta Enter verifică cuvântul, tasta Backspace
              șterge ultima literă, iar tasta Esc șterge toate
              literele. Atenție, dacă ați optat pentru diacritice, va
              trebui să le tastați ca atare.
            </p>
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
          joacă din nou
        </button>
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
