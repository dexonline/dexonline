{extends "layout.tpl"}

{block "title"}Parolă uitată{/block}

{block "search"}{/block}

{block "content"}
  <div class="card col-md-6 col-sm-8 mx-auto">
    <div class="card-header">
      Parolă uitată
    </div>

    <div class="card-body">
      <p>
        Introdu adresa de e-mail și îți vom trimite un e-mail cu instrucțiuni
        pentru recuperarea parolei.
      </p>

      <form method="post">

        <div class="input-group mb-3">
          <span class="input-group-text">
            {include "bits/icon.tpl" i=email}
          </span>
          <input
            class="form-control {if isset($errors.email)}is-invalid{/if}"
            type="text"
            name="email"
            value="{$email}"
            autofocus
            placeholder="adresa de e-mail">
          {include "bits/fieldErrors.tpl" errors=$errors.email|default:null}
        </div>

        <button class="btn btn-primary" type="submit" name="submitButton">
          trimite
        </button>

      </form>
    </div>
  </div>
{/block}
