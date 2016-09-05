<h3>Utilizator de test</h3>
<form method="post" action="{$wwwRoot}auth/login">
  <div class="form-group">
    <label for="fakeUserNick">
      Nume de utilizator
    </label>
    <input class="form-control" type="text" name="fakeUserNick" id="fakeUserNick" value="{$fakeUserNick}" size="20">
  </div>
  <div class="form-group">
    {section name="p" loop=$smarty.const.NUM_PRIVILEGES}
      {assign var="i" value=$smarty.section.p.index}
      {math equation="1 << x" x=$i assign="mask"}
      <div class="checkbox">
        <label>
          <input type="checkbox" name="priv[]" value="{$mask}">
          {$privilegeNames[$i]}
        </label>
      </div>
    {/section}
    <div class="checkbox">
      <label>
        <input id="allPriv" type="checkbox" name="allPriv" value="1">
        TOATE privilegiile
      </label>
    </div>
  </div>
  <input class="btn btn-warning" type=submit name="submitButton" value="Conectare ca utilizator de test"/>
</form>
