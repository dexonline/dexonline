{extends "layout.tpl"}

{block "title"}Autentificare{/block}

{block "search"}{/block}

{block "content"}
  {assign var="allowFakeUsers" value=$allowFakeUsers|default:false}

  {if $allowFakeUsers}
    {include "bits/fakeUser.tpl"}
  {/if}

  <div class="col-md-6 col-md-offset-3 col-sm-8 col-sm-offset-2">
    <div class="panel panel-default">
      <div class="panel-heading">
        Autentificare
      </div>

      <div class="panel-body">
        <form method="post">

          <div class="form-group {if isset($errors.nick)}has-error{/if}">
            <div class="input-group">
              <span class="input-group-addon">
                <i class="glyphicon glyphicon-user"></i>
              </span>
              <input
                class="form-control"
                type="text"
                name="nick"
                value="{$nick}"
                placeholder="numele de utilizator sau adresa de e-mail">
            </div>
            {include "bits/fieldErrors.tpl" errors=$errors.nick|default:null}
          </div>

          <div class="form-group {if isset($errors.password)}has-error{/if}">
            <div class="input-group">
              <span class="input-group-addon">
                <i class="glyphicon glyphicon-lock"></i>
              </span>
              <input
                class="form-control"
                type="password"
                name="password"
                placeholder="parola">
            </div>
            {include "bits/fieldErrors.tpl" errors=$errors.password|default:null}
          </div>

          <div class="checkbox">
            <label>
              <input type="checkbox" name="remember" value="1" {if $remember}checked{/if}>
              ține-mă autentificat un an
            </label>
          </div>

          <button class="btn btn-primary" type="submit" name="submitButton">
            autentificare
          </button>

          <a class="btn btn-link pull-right" href="parola-uitata">
            mi-am uitat parola
          </a>
        </form>
      </div>
    </div>

    <a href="register">vreau să îmi creez un cont</a>

  </div>

{/block}
