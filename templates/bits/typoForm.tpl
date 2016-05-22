<div id="typoDivCloseButton" onclick="document.getElementById('typoDiv').style.display = 'none'" title="anulează și închide"></div>

<label for="typoTextarea">Semnalează o greșeală:</label>

<form id="typoHtmlForm" method="post" action="" onsubmit="return submitTypoForm()">
  <textarea class="form-control" id="typoTextarea" cols="40" rows="3" placeholder="descrieți problema aici..."></textarea>
  <input class="btn btn-default" type="submit" value="Trimite">
  <input type="hidden" name="definitionId" value="{$definitionId}">
  <a class="btn btn-link" href="#" onclick="document.getElementById('typoDiv').style.display = 'none'">anulează</a>
</form><br/>

<div id="typoDivNote">Note:</div>

<ul>
  <li>Unele dicționare (de exemplu <i>Scriban</i>) sunt preluate cu grafia veche. Aceasta nu este o greșeală de tipar.</li>
  <li>
    În general, preluăm definițiile fără modificări, dar putem face comentarii pe marginea lor. Vă rugăm să nu ne semnalați greșeli semantice decât în
    situații evidente.
  </li>
</ul>
