<h3>Utilizator de test</h3>
<form method="post" action="{$wwwRoot}auth/login">
  <div class="form-group">
    <label for="fakeUserNick">
      Nume de utilizator
    </label>
    <input class="form-control" type="text" name="fakeUserNick" id="fakeUserNick" value="{$fakeUserNick}" size="20">
  </div>
  <div class="form-group">
    {foreach User::$PRIV_NAMES as $privValue => $privName}
      <div class="checkbox">
        <label>
          <input type="checkbox" name="priv[]" value="{$privValue}">
          {$privName}
        </label>
      </div>
    {/foreach}
    <div class="checkbox">
      <label>
        <input id="allPriv" type="checkbox" name="allPriv" value="1">
        TOATE privilegiile
      </label>
    </div>
  </div>
  <input class="btn btn-warning" type=submit name="submitButton" value="Conectare ca utilizator de test"/>
</form>
