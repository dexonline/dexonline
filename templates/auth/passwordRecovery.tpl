{extends "layout.tpl"}

{block "title"}Schimbarea parolei{/block}

{block "search"}{/block}

{block "content"}
  {if $user}
    <div class="col-md-6 col-md-offset-3 col-sm-8 col-sm-offset-2">
      <div class="panel panel-default">
        <div class="panel-heading">
          Schimbarea parolei
        </div>

        <div class="panel-body">
          <form method="post">

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

            <button class="btn btn-primary" type="submit" name="submitButton">
              schimbă
            </button>
          </form>
        </div>
      </div>
    </div>
  {/if}
{/block}
