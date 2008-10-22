<?php
require_once("../phplib/util.php");
util_assertNotMirror();
?>

<html><head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title>Semnalare greseala de tipar</title>
</head><body onLoad="if (document.frm) document.frm.problem.focus()">

<?php

// Parse or initialize the GET/POST arguments
$problem = util_getRequestParameter('problem');
$definitionId = util_getRequestIntParameter('definitionId');
$definition = Definition::load($definitionId);

if ($problem && $definitionId) {
  $typo = new Typo();
  $typo->definitionId = $definitionId;
  $typo->problem = $problem;
  $typo->save();

  echo '<font color=red><b>Eroarea a fost semnalată. Vă mulţumim!</b></font>';
} else {
  echo 'Vă rugăm să precizaţi care este greşeala de tipar (maxim 200 ' .
    'caractere). NU folosiţi acest formular pentru a raporta greşeli de ' .
    'ordin semantic.<br/><br/>';
  echo '<form name="frm" method="post" action="flag_typo.php">';
  echo 'Greşeala: <input type="text" name="problem" maxlength=200 '.
    'size=50><p>';
  echo '<input type="submit" value=" Trimite ">';
  echo "<input type=\"hidden\" name=\"definitionId\" value=\"$definitionId\">";
  echo '</form><p>';

  echo "Definiţia:<br/>";
  echo $definition->htmlRep;
}

?>
