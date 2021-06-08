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
        {include "bs/checkbox.tpl"
          name='priv[]'
          label=$privName
          divClass='mb-1'
          value=$privValue}
      {/foreach}

      {include "bs/checkbox.tpl"
        name='allPriv'
        label='TOATE privilegiile'
        divClass='mb-2'}

      <button
        class="btn btn-warning"
        type="submit">
        {include "bits/icon.tpl" i=login}
        autentificare ca utilizator de test
      </button>

    </form>
  </div>
</div>
