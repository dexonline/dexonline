{extends "layout.tpl"}

{block "title"}Autentificare{/block}

{block "search"}{/block}

{block "content"}
  {assign var="allowFakeUsers" value=$allowFakeUsers|default:false}

  <div class="col-md-6 col-sm-8 mx-auto">
    {if $allowFakeUsers}
      {include "bits/fakeUser.tpl"}
    {/if}

    <div class="card mb-3">
      <div class="card-header">
        Autentificare
      </div>

      <div class="card-body">
        <form method="post">

          <div class="input-group mb-3">
            <span class="input-group-text">
              {include "bits/icon.tpl" i=person}
            </span>
            <input
              class="form-control {if isset($errors.nick)}is-invalid{/if}"
              type="text"
              name="nick"
              value="{$nick}"
              autofocus
              placeholder="numele de utilizator sau adresa de e-mail">
            {include "bits/fieldErrors.tpl" errors=$errors.nick|default:null}
          </div>

          <div class="input-group mb-3">
            <span class="input-group-text">
              {include "bits/icon.tpl" i=lock}
            </span>
            <input
              class="form-control {if isset($errors.password)}is-invalid{/if}"
              type="password"
              name="password"
              placeholder="parola">
            {include "bits/fieldErrors.tpl" errors=$errors.password|default:null}
          </div>

          {include "bs/checkbox.tpl"
            name='remember'
            label='ține-mă autentificat un an'
            checked=$remember
            divClass='mb-3'}

          <div class="d-flex justify-content-between">
            <button class="btn btn-primary" type="submit" name="submitButton">
              {include "bits/icon.tpl" i=login}
              autentificare
            </button>

            <a class="btn btn-link" href="{Router::link('auth/lostPassword')}">
              mi-am uitat parola
            </a>
          </div>
        </form>
      </div>
    </div>

    <a
      class="btn btn-link"
      href="{Router::link('auth/register')}">
      mă înregistrez
    </a>

  </div>

{/block}
