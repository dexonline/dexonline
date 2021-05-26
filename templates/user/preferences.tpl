{extends "layout.tpl"}

{block "title"}Preferințe{/block}

{block "search"}{/block}

{block "content"}
  {if User::getActive()}
    <div class="card mb-3">
      <div class="card-header">Imagine</div>
      <div class="card-body">
        {include "bits/avatar.tpl" user=User::getActive()}

        <form
          action="{Router::link('user/editAvatar')}"
          method="post"
          enctype="multipart/form-data">

          <div class="d-flex mt-2">
            <label class="col-form-label">Fișier:</label>
            <div class="ms-2">
              <input class="form-control" id="avatarFileName" type="file" name="avatarFileName">
            </div>
            <button
              id="avatarSubmit"
              class="btn btn-primary ms-2"
              type="submit"
              name="submit"
              disabled>
              {include "bits/icon.tpl" i=edit}
              editează
            </button>

            {if User::getActive()->hasAvatar}
              <a href="{Router::link('user/saveAvatar')}?delete=1"
                class="btn btn-outline-danger ms-1"
                onclick="return confirm('Confirmați ștergerea imaginii?');">
                {include "bits/icon.tpl" i=delete}
                șterge imaginea
              </a>
            {/if}

          </div>

          <div class="form-text">
            Imaginea profilului tău are rezoluția de 48x48 pixeli.
            Pe ecranul următor poți edita poza încărcată.
          </div>
        </form>
      </div>
    </div>
  {/if}

  <form method="post">
    {if User::getActive()}
      <div class="card mb-3">
        <div class="card-header">Date personale</div>
        <div class="card-body">

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

          <div class="form-check">
            <label class="form-check-label">
              <input
                type="checkbox"
                class="form-check-input"
                name="detailsVisible"
                value="1"
                {if $detailsVisible}checked{/if}>
              datele mele sunt vizibile public
              <div class="form-text mb-3">
                Poți afișa sau ascunde numele și adresa de email în
                <a href="{Router::link('user/view')}/{User::getActive()}">profilul tău</a>.
              </div>
            </label>
          </div>

          <fieldset>
            <legend>dacă vrei să îți schimbi parola</legend>

            <div class="input-group mb-3">
              <span class="input-group-text">
                {include "bits/icon.tpl" i=lock}
              </span>
              <input
                class="form-control {if isset($errors.password)}is-invalid{/if}"
                type="password"
                name="password"
                value="{$password}"
                placeholder="parola curentă">
              {include "bits/fieldErrors.tpl" errors=$errors.password|default:null}
            </div>

            <div class="input-group mb-3">
              <span class="input-group-text">
                {include "bits/icon.tpl" i=lock}
              </span>
              <input
                class="form-control {if isset($errors.newPassword)}is-invalid{/if}"
                type="password"
                name="newPassword"
                value="{$newPassword}"
                placeholder="parola nouă">
              {include "bits/fieldErrors.tpl" errors=$errors.newPassword|default:null}
            </div>

            <div class="input-group mb-3">
              <span class="input-group-text">
                {include "bits/icon.tpl" i=lock}
              </span>
              <input
                class="form-control"
                type="password"
                name="newPassword2"
                value="{$newPassword2}"
                placeholder="parola nouă (din nou)">
            </div>
          </fieldset>

        </div>
      </div>
    {/if}

    <div class="card mb-3">
      <div class="card-header">Preferințe</div>
      <div class="card-body">
        {foreach $userPrefs as $value => $i}
          <div class="form-check mb-2">
            <label class="form-check-label">
              <input
                type="checkbox"
                class="form-check-input"
                name="userPrefs[]"
                value="{$value}"
                {if !$i.enabled}disabled{/if}
                {if $i.checked}checked{/if}>
              {$i.label}
              <div class="form-text">{$i.comment}</div>
            </label>
          </div>
        {/foreach}

        <div class="d-flex">
          <label class="col-form-label">Fila implicită</label>
          <div class="ms-2">
            {$tab=Session::getPreferredTab()}
            <select name="preferredTab" class="form-select">
              <option
                value="{Constant::TAB_RESULTS}"
                {if $tab == Constant::TAB_RESULTS}selected{/if}>
                rezultate
              </option>
              <option
                value="{Constant::TAB_PARADIGM}"
                {if $tab == Constant::TAB_PARADIGM}selected{/if}>
                flexiuni
              </option>
              <option
                value="{Constant::TAB_TREE}"
                {if $tab == Constant::TAB_TREE}selected{/if}>
                sinteză
              </option>
            </select>
          </div>
        </div>
      </div>
    </div>

    <div class="card mb-3">
      <div class="card-header">Elemente în pagina principală</div>
      <div class="card-body row">

        {foreach $widgets as $value => $w}
          <div class="col-6 col-md-4">
            <div class="form-check">
              <label class="form-check-label">
                <input
                  type="checkbox"
                  class="form-check-input"
                  name="widgets[]"
                  value="{$value}"
                  {if $w.enabled}checked{/if}>
                {$w.name}
              </label>
            </div>
          </div>
        {/foreach}
      </div>
    </div>

    {if User::can(User::PRIV_ANY)}
      <div class="card mb-3">
        <div class="card-header">Privilegii</div>
        <div class="card-body">
          <ul class="mb-0">
            {foreach User::PRIV_NAMES as $mask => $privName}
              {if User::can($mask)}
                <li>{$privName}</li>
              {/if}
            {/foreach}
          </ul>
        </div>
      </div>
    {/if}

    <button class="btn btn-primary" type="submit" name="saveButton">
      {include "bits/icon.tpl" i=save}
      salvează
    </button>
    {if User::getActive()}
      <a class="btn btn-link" href="{Router::link('user/view')}/{User::getActive()|escape}">
        renunță</a>
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
