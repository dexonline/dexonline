<div class="card mb-3">
  <div class="card-header">
    Autentificare ca utilizator de test
  </div>

  <div class="card-body">
    <form method="post">

      <div class="input-group mb-2">
        <span class="input-group-text">
          {include "bits/icon.tpl" i=person}
        </span>
        <input
          class="form-control"
          type="text"
          name="fakeUserNick"
          value="{$fakeUserNick}"
          placeholder="nume de utilizator">
      </div>

      {foreach User::PRIV_NAMES as $privValue => $privName}
        <div class="form-check mb-1">
          <label class="form-check-label">
            <input type="checkbox" class="form-check-input" name="priv[]" value="{$privValue}">
            {$privName}
          </label>
        </div>
      {/foreach}

      <div class="form-check mb-2">
        <label class="form-check-label">
          <input id="allPriv" type="checkbox" class="form-check-input" name="allPriv" value="1">
          TOATE privilegiile
        </label>
      </div>

      <button
        class="btn btn-warning"
        type="submit">
        {include "bits/icon.tpl" i=login}
        autentificare ca utilizator de test
      </button>

    </form>
  </div>
</div>
