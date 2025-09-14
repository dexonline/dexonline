{extends "layout.tpl"}

{block "title"}{t}Sign up{/t}{/block}

{block "search"}{/block}

{block "content"}
  <div class="card col-md-6 col-sm-8 mx-auto">
    <div class="card-header">
      {t}Sign up{/t}
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
            placeholder="{t}username or email address{/t}">
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
            placeholder="{t}password{/t}">
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
            placeholder="{t}confirm password{/t}">
        </div>

        <fieldset>
          <legend>{t}optional{/t}</legend>

          <div class="input-group">
            <span class="input-group-text">
              {include "bits/icon.tpl" i=email}
            </span>
            <input
              class="form-control {if isset($errors.email)}is-invalid{/if}"
              type="text"
              name="email"
              value="{$email}"
              placeholder="{t}email address{/t}">
            {include "bits/fieldErrors.tpl" errors=$errors.email|default:null}
          </div>
          <div class="form-text mb-3">
            {t}This email address is used for account recovery only.{/t}
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
              placeholder="{t}name{/t}">
          </div>

          {* We must capture the translated string first, as Smarty won't evaluate {t} tags in parameters *}
          {capture name="remember_me_label"}{t}keep me logged in for a year{/t}{/capture}
          {include "bs/checkbox.tpl"
            name='remember'
            label=$smarty.capture.remember_me_label
            checked=$remember
            divClass='mb-3'}

        </fieldset>

        <div class="d-flex justify-content-between">
          <button class="btn btn-primary" type="submit" name="submitButton">
            {include "bits/icon.tpl" i=login}
            {t}sign up{/t}
          </button>

          <a class="btn btn-link" href="{Router::link('auth/login')}">
            {t}already have an account{/t}
          </a>
        </div>
      </form>
    </div>
  </div>

{/block}
