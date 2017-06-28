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

        <div>
          <button id="startGameButton" class="btn btn-success" type="button" disabled>
            <i class="glyphicon glyphicon-play"></i>
            începe
          </button>
          <button id="optionsButton"
                  class="btn btn-default"
                  type="button"
                  data-toggle="collapse"
                  data-target="#optionsDiv">
            <i class="glyphicon glyphicon-wrench"></i>
            opțiuni
          </button>
        </div>

        <div id="optionsDiv" class="voffset3 collapse">

          <div class="form-group">
            <label class="col-sm-2 control-label">mod</label>
            <div class="col-sm-10">
              <div class="btn-group" data-toggle="buttons">
                <label class="btn btn-info active">
                  <input type="radio" name="mode" value="0"> toate cuvintele
                </label>
                <label class="btn btn-info">
                  <input type="radio" name="mode" value="1"> doar anagrame
                </label>
              </div>
            </div>
          </div>

          <div class="form-group">
            <label class="col-sm-2 control-label">nivel</label>
            <div class="col-sm-10">
              <div class="btn-group" data-toggle="buttons">
                <label class="btn btn-info">
                  <input type="radio" name="level" value="4"> 4 litere
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
                  <input type="radio" name="useDiacritics" value="0"> nu
                </label>
                <label class="btn btn-info">
                  <input type="radio" name="useDiacritics" value="1"> da
                </label>
              </div>
            </div>
          </div>

          <div class="form-group">
            <label class="col-sm-2 control-label">timp</label>
            <div class="col-sm-10">
              <div class="btn-group" data-toggle="buttons">
                <label class="btn btn-info">
                  <input type="radio" name="seconds" value="60"> un minut
                </label>
                <label class="btn btn-info">
                  <input type="radio" name="seconds" value="120"> 2 minute
                </label>
                <label class="btn btn-info active">
                  <input type="radio" name="seconds" value="180"> 3 minute
                </label>
                <label class="btn btn-info">
                  <input type="radio" name="seconds" value="240"> 4 minute
                </label>
                <label class="btn btn-info">
                  <input type="radio" name="seconds" value="300"> 5 minute
                </label>
              </div>
            </div>
          </div>
        </div>

        <h3>Scopul jocului</h3>
        <p>
          Formați cât de multe cuvinte puteți cu literele primite,
          până se termină timpul.
        </p>

        <p>
          Sunt două moduri de joc:
        </p>

        <ol>
          <li>
            <b>Toate cuvintele:</b>
            găsiți toate cuvintele de cel puțin trei litere.
          </li>
          <li>
            <b>Doar anagrame:</b>
            găsiți un cuvânt care folosește toate literele, apoi
            primiți alt set de litere.
          </li>
        </ol>

        <h3>Instrucțiuni</h3>
        <p>
          Dacă folosiți tastatura, tastați pur și simplu
          literele. Dacă folosiți mouse-ul, dați click pe litere.
        </p>

        <p>
          Cuvintele includ și conjugări (ca <i>lucrez)</i> sau
          declinări (ca <i>verzi).</i> Dacă ați optat pentru
          diacritice, atunci literele Ă, Â, Î, Ș și Ț nu pot fi
          interschimbate cu A, I, S și T.
        </p>

        <p>
          Tasta Enter verifică cuvântul, tasta Backspace șterge ultima
          literă, iar tasta Esc șterge toate literele. 
        </p>

        <p>
          Sub zona de joc se află:
        </p>

        <ul>
          <li>
            <i class="glyphicon glyphicon-hourglass text-big vcenter"></i>
            timpul rămas;
          </li>
          <li>
            <i class="glyphicon glyphicon-eye-open text-big vcenter"></i>
            numărul de cuvinte găsite și totalul de cuvinte posibile;
          </li>
          <li>
            <i class="glyphicon glyphicon-piggy-bank text-big vcenter"></i>
            scorul (cuvintele mai lungi primesc mai multe puncte).
          </li>
        </ul>

      </form>
    </div>
  </div>

  <div id="gamePanel" class="panel panel-default">
    <div class="panel-heading">
      Omleta cuvintelor
    </div>

    <div class="panel-body">
      <canvas width="480" height="320"></canvas>

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
