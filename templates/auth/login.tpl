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

    <p>
      <a href="register">vreau să îmi creez un cont</a>
      <a href="#openidInfo" class="pull-right" data-toggle="collapse">
        informații despre OpenID
      </a>
    </p>

    <div id="openidInfo" class="collapse">
      <div class="well">
        <p>
          <i>dexonline</i> a permis autentificarea cu OpenID (și doar cu
          OpenID) între 2011 și 2018. OpenID promitea să fie un standard
          deschis și descentralizat pentru autentificare. Marele avantaj ar fi
          fost că utilizatorii puteau folosi același cont pe toate site-urile,
          fără a mai memora zeci de conturi și parole.
        </p>

        <p>
          Din păcate, OpenID nu a avut succesul dorit. Marii furnizori de
          identități pe Internet (în primul rând Google și Facebook) l-au
          înlocuit fiecare cum a crezut de cuviință, folosind tehnologii
          divergente. Pentru <i>dexonline</i>, unde timpul de dezvoltare este
          limitat, nu se justifică energia necesară întreținerii tuturor
          acestor tehnologii. În plus, ambele companii au devenit obsedate de
          urmărirea activității utilizatorilor. Nu dorim să încurajăm acest
          model forțând utilizatorii să se autentifice cu Google sau Facebook.
        </p>

        <p>
          De aceea am luat decizia să revenim la modelul clasic, de conturi cu
          nume și parolă doar pentru dexonline. Dacă aveți un cont la noi, vă
          puteți recupera parola accesând link-ul
          <a href="parola-uitata">mi-am uitat parola</a>.
        </p>

        <p>
          Știm că gestionarea atâtor conturi devine o problemă. Vă recomandăm
          să încercați un manager de parole cum ar fi
          <a href="https://keepass.info/">KeePass</a> sau
          <a href="https://www.keepassx.org/">KeePassX</a>.
        </p>
      </div>
    </div>

  </div>

{/block}
