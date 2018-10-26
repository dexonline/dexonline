<div class="col-md-6 col-md-offset-3 col-sm-8 col-sm-offset-2">
  <div class="panel panel-default">
    <div class="panel-heading">
      Autentificare ca utilizator de test
    </div>

    <div class="panel-body">
      <form method="post">

        <div class="form-group">
          <div class="input-group">
            <span class="input-group-addon">
              <i class="glyphicon glyphicon-user"></i>
            </span>
            <input
              class="form-control"
              type="text"
              name="fakeUserNick"
              value="{$fakeUserNick}"
              placeholder="nume de utilizator">
          </div>
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

        <input
          class="btn btn-warning"
          type=submit
          value="autentificare ca utilizator de test">

      </form>
    </div>
  </div>
</div>
