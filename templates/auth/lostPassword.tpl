{extends "layout.tpl"}

{block "title"}{t}Forgot your password?{/t}{/block}

{block "search"}{/block}

{block "content"}
  <div class="card col-md-6 col-sm-8 mx-auto">
    <div class="card-header">
      {t}Forgot your password?{/t}
    </div>

    <div class="card-body">
      <p>
        {t}Enter your email address and we will send you an email with instructions on how to recover your password.{/t}
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
            placeholder="{t}email address{/t}">
          {include "bits/fieldErrors.tpl" errors=$errors.email|default:null}
        </div>

        <button class="btn btn-primary" type="submit" name="submitButton">
          {t}send{/t}
        </button>

      </form>
    </div>
  </div>
{/block}
