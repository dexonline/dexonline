<p class="paragraphTitle">Utilizator de test</p>
<form method="post" action="{$wwwRoot}auth/login">
  Nume de utilizator: <input type="text" name="fakeUserNick" value="{$fakeUserNick}" size="20"> <br>
  {section name="p" loop=$smarty.const.NUM_PRIVILEGES}
    {assign var="i" value=$smarty.section.p.index}
    {math equation="1 << x" x=$i assign="mask"}
     <input id="priv_{$i}"type="checkbox" name="priv[]" value="{$mask}">
     <label for="priv_{$i}">{$privilegeNames[$i]}</label>
     <br>
  {/section}
  <input id="allPriv" type="checkbox" name="allPriv" value="1">
  <label for="allPriv">TOATE privilegiile</label>
  <br/>
  <input type=submit name="submitButton" value="Conectare ca utilizator de test"/>
</form>
