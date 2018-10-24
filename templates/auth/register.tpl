{extends "layout.tpl"}

{block "title"}Înregistrare{/block}

{block "search"}{/block}

{block "content"}
  <div class="col-md-6 col-md-offset-3 col-sm-8 col-sm-offset-2">
    <div class="panel panel-default">
      <div class="panel-heading">
        Înregistrare
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
                placeholder="numele de utilizator">
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
                value="{$password}"
                placeholder="parola">
            </div>
            <div class="input-group voffset3">
              <span class="input-group-addon">
                <i class="glyphicon glyphicon-lock"></i>
              </span>
              <input
                class="form-control"
                type="password"
                name="password2"
                value="{$password2}"
                placeholder="parola (din nou)">
            </div>
            {include "bits/fieldErrors.tpl" errors=$errors.password|default:null}
          </div>

          <fieldset>
            <legend><h5>opțional</h5></legend>

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
                  placeholder="adresa de e-mail*">
              </div>
              {include "bits/fieldErrors.tpl" errors=$errors.email|default:null}
            </div>
            <p class="help-block">
              *folosită <b>doar</b> ca să îți poți recupera contul dacă îți uiți parola
            </p>

            <div class="form-group">
              <div class="input-group">
                <span class="input-group-addon">
                  <i class="glyphicon glyphicon-user"></i>
                </span>
                <input
                  class="form-control"
                  type="text"
                  name="name"
                  value="{$name}"
                  placeholder="numele real">
              </div>
            </div>            

            <div class="checkbox">
              <label>
                <input type="checkbox" name="remember" value="1" {if $remember}checked{/if}>
                ține-mă autentificat un an
              </label>
            </div>
          </fieldset>

          <button class="btn btn-primary" type="submit" name="submitButton">
            înregistrare
          </button>

          <a class="btn btn-link pull-right" href="login">
            am deja un cont
          </a>
        </form>
      </div>
    </div>
  </div>

{/block}
