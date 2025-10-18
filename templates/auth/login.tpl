{extends "layout.tpl"}

{block "title"}{t}login{/t}{/block}

{block "search"}{/block}

{block "content"}
  {assign var="allowFakeUsers" value=$allowFakeUsers|default:false}

  <div class="col-md-6 col-sm-8 mx-auto">
    {if $allowFakeUsers}
      {include "bits/fakeUser.tpl"}
    {/if}

    <div class="card mb-3">
      <div class="card-header">
        {t}Login{/t}
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
              placeholder="{t}password{/t}">
            {include "bits/fieldErrors.tpl" errors=$errors.password|default:null}
          </div>

          {* We must capture the translated string first, as Smarty won't evaluate {t} tags in parameters *}
          {capture name="remember_me_label"}{t}keep me logged in for a year{/t}{/capture}
          {include "bs/checkbox.tpl"
            name='remember'
            label=$smarty.capture.remember_me_label
            checked=$remember
            divClass='mb-3'}

          <div class="d-flex justify-content-between">
            <button class="btn btn-primary" type="submit" name="submitButton">
              {include "bits/icon.tpl" i=login}
              {t}login{/t}
            </button>

            <a class="btn btn-link" href="{Router::link('auth/lostPassword')}">
              {t}forgot my password{/t}
            </a>
          </div>
        </form>
      </div>
    </div>

    <a
      class="btn btn-link"
      href="{Router::link('auth/register')}">
      {t}create an account{/t}
    </a>

  </div>

{/block}
