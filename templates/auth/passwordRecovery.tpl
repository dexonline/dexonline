{extends "layout.tpl"}

{block "title"}Schimbarea parolei{/block}

{block "search"}{/block}

{block "content"}
  {if $user}
    <div class="card col-md-6 col-sm-8 mx-auto">
      <div class="card-header">
        Schimbarea parolei
      </div>

      <div class="card-body">
        <form method="post">

          <div class="input-group mb-3">
            <span class="input-group-text">
              {include "bits/icon.tpl" i=lock}
            </span>
            <input
              class="form-control {if isset($errors.password)}is-invalid{/if}"
              type="password"
              name="password"
              value="{$password}"
              placeholder="parola">
            {include "bits/fieldErrors.tpl" errors=$errors.password|default:null}
          </div>

          <div class="input-group mb-3">
            <span class="input-group-text">
              {include "bits/icon.tpl" i=lock}
            </span>
            <input
              class="form-control"
              type="password"
              name="password2"
              value="{$password2}"
              placeholder="parola (din nou)">
          </div>

          <button class="btn btn-primary" type="submit" name="submitButton">
            schimbă
          </button>
        </form>
      </div>
    </div>
  {/if}
{/block}
