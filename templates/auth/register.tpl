{extends "layout.tpl"}

{block "title"}Înregistrare{/block}

{block "search"}{/block}

{block "content"}
  <div class="card col-md-6 col-sm-8 mx-auto">
    <div class="card-header">
      Înregistrare
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
            placeholder="numele de utilizator">
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

        <fieldset>
          <legend>opțional</legend>

          <div class="input-group">
            <span class="input-group-text">
              {include "bits/icon.tpl" i=email}
            </span>
            <input
              class="form-control {if isset($errors.email)}is-invalid{/if}"
              type="text"
              name="email"
              value="{$email}"
              placeholder="adresa de e-mail">
            {include "bits/fieldErrors.tpl" errors=$errors.email|default:null}
          </div>
          <div class="form-text mb-3">
            folosită <b>doar</b> ca să îți poți recupera contul dacă îți uiți parola
          </div>

          <div class="input-group mb-3">
            <span class="input-group-text">
              {include "bits/icon.tpl" i=badge}
            </span>

            <input
              class="form-control"
              type="text"
              name="name"
              value="{$name}"
              placeholder="numele real">
          </div>

          {include "bs/checkbox.tpl"
            name='remember'
            label='ține-mă autentificat un an'
            checked=$remember
            divClass='mb-3'}
        </fieldset>

        <div class="d-flex justify-content-between">
          <button class="btn btn-primary" type="submit" name="submitButton">
            {include "bits/icon.tpl" i=login}
            înregistrare
          </button>

          <a class="btn btn-link" href="{Router::link('auth/login')}">
            am deja un cont
          </a>
        </div>
      </form>
    </div>
  </div>

{/block}
