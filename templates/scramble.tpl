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
          <label class="col-sm-2 control-label">dificultate</label>
          <div class="col-sm-10">
            <button class="btn btn-info" type="button" value="4">foarte ușor</button>
            <button class="btn btn-info" type="button" value="5">ușor</button>
            <button class="btn btn-info" type="button" value="6">normal</button>
            <button class="btn btn-info" type="button" value="7">greu</button>
          </div>
        </div>

        <div class="form-group">
          <div class="col-sm-offset-2 col-sm-10">
            <div class="checkbox">
              <label>
                <input id="toggleD" type="checkbox"> diacritice
              </label>
            </div>
          </div>
        </div>
      </form>
    </div>
  </div>

  <div id="gameArea" class="panel panel-default">
    <div class="panel-heading">
      Omleta cuvintelor
    </div>

    <div class="panel-body">
      <div id="resultSearch" align="center">
        <span>Timp ramas: </span>
        <span id="timer"> 0 </span>
        <span>Scor: </span>
        <span id="score"> 0 </span>
        <span>Nr. cuvinte:</span>
        <span id="maxWords">0</span>
      </div>

      <div class="drawArea">
        <canvas class="canvasArea" width='480' height='280' ></canvas>
        <br>
        <button class="wordBtn btn btn-default" type="button">Dă-mi cuvintele</button>
        <br>
        <table class="table table-responsive wordArea" style="display:none;">
          <caption>Cuvintele posibile sunt</caption>
          <tr class="wordList"></tr>
        </table>
      </div>

    </div>
  </div>
{/block}
