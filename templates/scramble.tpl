{extends file="layout.tpl"}

{block name=title}Omleta Cuvintelor{/block}

{block name=content}
<div class="scrambleArea">
<p class="paragraphTitle"> Omleta Cuvintelor </p>
	<div id="mainPage">
    <form id="scramble" action="">Nivel dificultate:
      <button class="difficultyButton btn" type="button" value="1">Foarte usor</button>
      <button class="difficultyButton btn" type="button" value="2">Usor</button>
      <button class="difficultyButton btn" type="button" value="3">Normal</button>
      <button class="difficultyButton btn" type="button" value="4">Greu</button>
      <button class="difficultyButton btn" type="button" value="5">"Sunt dictionar"</button>
    </form>
    <div id="resultSearch" align="center">          
      <span>Timp ramas: </span>
      <span id="timer"> 0 </span>
      <span>Scor: </span>
      <span id="score"> 0 </span>
      <span>Nr cuvinte:</span>
      <span id="maxWords">0</span>
    </div>
    <div class="drawArea">
      <canvas class="canvasArea" width='480' height='280' ></canvas>
      <br/>
      <button class="wordBtn" type="button">Da-mi cuvintele</button>
      <table cellpadding="0" cellspacing="0" border="0" class="wordArea">Cuvintele posibile sunt: </br>
        <tr class="wordList"></tr>
      </table>
    </div>
  </div>
 </div>
</div>
{/block}

