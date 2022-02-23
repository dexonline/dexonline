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

          {capture "help"}
          Poți afișa sau ascunde numele și adresa de email în
          <a href="{Router::link('user/view')}/{User::getActive()}">profilul tău</a>.
          {/capture}

          {include "bs/checkbox.tpl"
            name=detailsVisible
            label='detaliile mele sunt vizibile public'
            checked=$detailsVisible
            divClass='mb-3'
            help=$smarty.capture.help}

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
          {include "bs/checkbox.tpl"
            name="userPrefs[]"
            label=$i.label
            checked=$i.checked
            disabled=!$i.enabled
            divClass='mb-3'
            help=$i.comment
            value=$value}
        {/foreach}
      </div>
    </div>

    <div class="card mb-3">
      <div class="card-header">Fila preferată</div>

      <div class="card-body">
        <p>
          Trageți de file pentru a le reordona. La căutări, vom afișa prima filă
          disponibilă. Nu toate filele există tot timpul; de exemplu nu
          întotdeauna vor exista imagini.
        </p>

        <ul id="tab-order" class="list-group sortable mb-3">
          {foreach Session::getTabs() as $tab}
            <li
              class="list-group-item"
              data-default-order="{Tab::getDefaultOrderPosition($tab)}">

              <input type="hidden" name="tabs[]" value="{$tab}">
              {Tab::getName($tab)}

            </li>
          {/foreach}
        </ul>

        <a href="#" id="restore-tab-order-link">
          revino la ordinea implicită
        </a>
      </div>
    </div>

    <div class="card mb-3">
      <div class="card-header">Elemente în pagina principală</div>
      <div class="card-body row">

        {foreach $widgets as $value => $w}
          <div class="col-6 col-md-4">
            {include "bs/checkbox.tpl"
              name="widgets[]"
              label=$w.name
              checked=$w.enabled
              value=$value}
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
