{extends "layout.tpl"}

{block "title"}Parolă uitată{/block}

{block "search"}{/block}

{block "content"}
  <div class="col-md-6 col-md-offset-3 col-sm-8 col-sm-offset-2">
    <div class="panel panel-default">
      <div class="panel-heading">
        Parolă uitată
      </div>

      <div class="panel-body">
        <p>
          Introdu adresa de e-mail și îți vom trimite un e-mail cu instrucțiuni
          pentru recuperarea parolei.
        </p>

        <form method="post">

          <div class="form-group {if isset($errors.email)}has-error{/if}">
            <div class="input-group">
              <span class="input-group-addon">
                <i class="glyphicon glyphicon-envelope"></i>
              </span>
              <input
                class="form-control"
                type="text"
                name="email"
                value="{$email}"
                placeholder="adresa de e-mail">
            </div>
            {include "bits/fieldErrors.tpl" errors=$errors.email|default:null}
          </div>

          <button class="btn btn-primary" type="submit" name="submitButton">
            trimite
          </button>

        </form>
      </div>
    </div>
  </div>
{/block}
