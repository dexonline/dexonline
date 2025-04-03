{extends "layout.tpl"}


{block "title"}
    {cap}Limba de lemn{/cap}
{/block}

{block "content"}
<h1>Limba de lemn</h1>
<p>Din tabelul de mai jos, începeți să citiți orice căsuță din coloana 1, apoi treceți la oricare căsuță din coloana 2,
apoi la oricare alta din 3, apoi mergeți unde vreți în 4, pentru ca pe urmă să reveniți oriunde doriți în coloana 1
și tot așa, aleatoriu… Vă opriți când le terminați pe toate sau vă opriți când vreți… tot un drac!
  Dar, mai ales, nu uitați să folosiți intonația și forța de convingere.</p>

<table class="table table-bordered wood"></table>
<br/>
<h3>Încercați și dvs.</h3>

<button class="btn btn-primary" onClick="document.getElementById('llText').innerText = genereazaLinie(limbaDeLemnCol);">
  Generează o linie
</button>

<button class="btn btn-primary" onClick="document.getElementById('llText').innerText = genereazaDiscurs(limbaDeLemnCol);">
  Generează un discurs
</button>

<br/><br/>
<div id="llText" class="def"></div>

<script type="text/javascript">
  var listLimbaDeLemn = [];
  for (var i = 0; i < limbaDeLemn.length; i++) {
    row = limbaDeLemn[i];
    var rowHash = {};
    for (var colIndex = 0; colIndex < row.length; colIndex++) {
      rowHash[colIndex+1] = row[colIndex]
    }
    listLimbaDeLemn.push(rowHash);
  }

  buildHtmlTable('.wood', listLimbaDeLemn);

  var limbaDeLemnCol = transpose(limbaDeLemn);
</script>

{/block}

{block "content"}

{/block}
