{extends "layout.tpl"}

{block "title"}Preferințe{/block}

{block "content"}
  {if User::getActive()}
    <div class="panel panel-default">
      <div class="panel-heading">Imagine</div>
      <div class="panel-body">
        <form action="editare-avatar" method="post" enctype="multipart/form-data">
          {include "bits/avatar.tpl" user=User::getActive()}
          <br>
          <br>
          <div class="form-group">
            <label for="avatarFileName">Fișier:</label>
            <input id="avatarFileName" type="file" name="avatarFileName">
          </div>
          <button id="avatarSubmit" class="btn btn-default" type="submit" name="submit" disabled>
            <i class="glyphicon glyphicon-pencil"></i>
            editează
          </button>
          {if User::getActive()->hasAvatar}
            <a href="salvare-avatar?delete=1"
               class="btn btn-danger"
               onclick="return confirm('Confirmați ștergerea imaginii?');">
              <i class="glyphicon glyphicon-trash"></i>
              șterge imaginea
            </a>
          {/if}

          <p class="help-block">
            Imaginea profilului tău are rezoluția de 48x48 pixeli.
            Pe ecranul următor poți edita poza încărcată.
          </p>
        </form>
      </div>
    </div>
  {/if}

  <form method="post">
    {if User::getActive()}
      <div class="panel panel-default">
        <div class="panel-heading">Date personale</div>
        <div class="panel-body">

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
              <input type="checkbox" name="detailsVisible" value="1" {if $detailsVisible}checked{/if}>
              datele mele sunt vizibile public
              <span class="help-block">
                Poți afișa sau ascunde numele și adresa de email în
                <a href="{$wwwRoot}utilizator/{User::getActive()}">profilul tău</a>.
              </span>
            </label>
          </div>

          <fieldset>
            <legend><h5>dacă vrei să îți schimbi parola</h5></legend>

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
                  placeholder="parola curentă">
              </div>
              {include "bits/fieldErrors.tpl" errors=$errors.password|default:null}
            </div>

            <div class="form-group {if isset($errors.newPassword)}has-error{/if}">
              <div class="input-group">
                <span class="input-group-addon">
                  <i class="glyphicon glyphicon-lock"></i>
                </span>
                <input
                  class="form-control"
                  type="password"
                  name="newPassword"
                  value="{$newPassword}"
                  placeholder="parola nouă">
              </div>
              <div class="input-group voffset3">
                <span class="input-group-addon">
                  <i class="glyphicon glyphicon-lock"></i>
                </span>
                <input
                  class="form-control"
                  type="password"
                  name="newPassword2"
                  value="{$newPassword2}"
                  placeholder="parola nouă (din nou)">
              </div>
              {include "bits/fieldErrors.tpl" errors=$errors.newPassword|default:null}
            </div>
          </fieldset>

        </div>
      </div>
    {/if}

    <div class="panel panel-default">
      <div class="panel-heading">Preferințe</div>
      <div class="panel-body">
        {foreach $userPrefs as $value => $i}
          <div class="checkbox {if !$i.enabled}disabled{/if}">
            <label>
              <input type="checkbox"
                     name="userPrefs[]"
                     value="{$value}"
                     {if !$i.enabled}disabled{/if}
                     {if $i.checked}checked{/if}>
              {$i.label}
              <span class="help-block">{$i.comment}</span>
            </label>
          </div>
        {/foreach}
      </div>
    </div>

    <div class="panel panel-default">
      <div class="panel-heading">Elemente în pagina principală</div>
      <div class="panel-body">

        {foreach $widgets as $value => $w}
          <div class="checkbox">
            <label>
              <input type="checkbox" name="widgets[]" value="{$value}" {if $w.enabled}checked{/if}>
              {$w.name}
            </label>
          </div>
        {/foreach}
      </div>
    </div>

    {if User::can(User::PRIV_ANY)}
      <div class="panel panel-default">
        <div class="panel-heading">Privilegii</div>
        <div class="panel-body">
          <ul>
            {foreach User::$PRIV_NAMES as $mask => $privName}
              {if User::can($mask)}
                <li>{$privName}</li>
              {/if}
            {/foreach}
          </ul>
        </div>
      </div>
    {/if}

    <button class="btn btn-success" type="submit" name="saveButton">
      <i class="glyphicon glyphicon-floppy-disk"></i>
      salvează
    </button>
    {if User::getActive()}
      <a class="btn btn-link" href="{$wwwRoot}utilizator/{User::getActive()|escape}">renunță</a>
    {/if}

  </form>

  <script>
   $('#avatarFileName').change(function() {
     var error = '';
     var allowedTypes = ['image/gif', 'image/jpeg', 'image/png'];
     if (this.files[0].size > (1 << 21)) {
       error = 'Dimensiunea maximă admisă este 2 MB.';
     } else if (allowedTypes.indexOf(this.files[0].type) == -1) {
       error = 'Sunt permise doar imagini jpeg, png sau gif.';
     }
     if (error) {
       $('#avatarFileName').val('');
       $('#avatarSubmit').attr('disabled', 'disabled');
       alert(error);
     } else {
       $('#avatarSubmit').removeAttr('disabled');
     }
     return false;
   });
  </script>
{/block}
