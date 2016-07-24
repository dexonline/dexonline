{extends file="layout.tpl"}

{block name=title}Omleta Cuvintelor{/block}

{block name=content}
  <div class="scrambleArea">
    <p class="paragraphTitle"> Omleta Cuvintelor </p>
    <div id="mainPage">
      <form id="scramble" action="">
        <dl class="dl-horizontal">
          <dt>Nivel dificultate</dt>
          <dd>
            <button class="btn btn-info" type="button" value="1">Foarte usor</button>
            <button class="btn btn-info" type="button" value="2">Usor</button>
            <button class="btn btn-info" type="button" value="3">Normal</button>
            <button class="btn btn-info" type="button" value="4">Greu</button>
            <button class="btn btn-warning" type="button" value="5">"Sunt dictionar"</button>
          </dd>
          <dt><label for="toggleD">Diacritice?</label></dt>
          <dd>
            <input id="toggleD" type="checkbox" />
          </dd>
        </dl>
      </form>
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
        <br/>
        <button class="wordBtn btn btn-default" type="button">Dă-mi cuvintele</button>
        <br />
        <table class="table table-responsive wordArea" style="display:none;">
          <caption>Cuvintele posibile sunt</caption>
          <tr class="wordList"></tr>
        </table>
      </div>
    </div>
  </div>
  </div>
{/block}
