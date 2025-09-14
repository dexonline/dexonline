{extends "layout.tpl"}

{block "title"}{t}preferences{/t}{/block}

{block "search"}{/block}

{block "content"}
  {if User::getActive()}
    <div class="card mb-3">
      <div class="card-header">{t}Image{/t}</div>
      <div class="card-body">
        {include "bits/avatar.tpl" user=User::getActive()}

        <form
          action="{Router::link('user/editAvatar')}"
          method="post"
          enctype="multipart/form-data">

          <div class="d-flex mt-2">
            <label class="col-form-label">{t}File{/t}:</label>
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
              {t}edit{/t}
            </button>

            {if User::getActive()->hasAvatar}
              <a href="{Router::link('user/saveAvatar')}?delete=1"
                class="btn btn-outline-danger ms-1"
                onclick="return confirm('Confirmați ștergerea imaginii?');">
                {include "bits/icon.tpl" i=delete}
                {t}delete image{/t}
              </a>
            {/if}

          </div>

          <div class="form-text">
            {t}The resolution of your profile picture must be 48x48 pixels.{/t}
            {t}On the next screen, you can edit the uploaded photo.{/t}
          </div>
        </form>
      </div>
    </div>
  {/if}

  <form method="post">
    {if User::getActive()}
      <div class="card mb-3">
        <div class="card-header">{t}Personal data{/t}</div>
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

          {capture "help"}
          {t 1=Router::link('user/view') 2=User::getActive()}You can show or hide your name and email address in
          <a href="%1/%2">your profile</a>.{/t}
          {/capture}

          {include "bs/checkbox.tpl"
            name=detailsVisible
            label='detaliile mele sunt vizibile public'
            checked=$detailsVisible
            divClass='mb-3'
            help=$smarty.capture.help}

          <fieldset>
            <legend>{t}if you want to change your password{/t}</legend>

            <div class="input-group mb-3">
              <span class="input-group-text">
                {include "bits/icon.tpl" i=lock}
              </span>
              <input
                class="form-control {if isset($errors.password)}is-invalid{/if}"
                type="password"
                name="password"
                value="{$password}"
                placeholder="{t}current password{/t}">
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
                placeholder="{t}new password{/t}">
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
                placeholder="{t}confirm password{/t}">
            </div>
          </fieldset>

        </div>
      </div>
    {/if}

    <div class="card mb-3">
      <div class="card-header">{t}Settings{/t}</div>
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
      <div class="card-header">{t}Favorite tabs{/t}</div>

      <div class="card-body">
        <p>
          {t}Drag and drop tabs to reorder them. For searches, we will display the first available tab. Not all tabs are available at all times; for example, images will not always be available.{/t}
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

        <a href="#" class="btn btn-outline-secondary" id="restore-tab-order-link">
          {t}restore default order{/t}
        </a>
      </div>
    </div>

    <div class="card mb-3">
      <div class="card-header">{t}Elements on the main page{/t}</div>
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
      {t}save{/t}
    </button>
    {if User::getActive()}
      <a class="btn btn-link" href="{Router::link('user/view')}/{User::getActive()|escape}">
        {t}cancel{/t}
      </a>
    {/if}

  </form>

  <script>
    $('#avatarFileName').change(function() {
      var error = '';
      var allowedTypes = ['image/gif', 'image/jpeg', 'image/png'];
      if (this.files[0].size > (1 << 21)) {
        error = '{t}The maximum size allowed is 2 MB.{/t}';
      } else if (allowedTypes.indexOf(this.files[0].type) == -1) {
        error = '{t}Only jpeg, png, or gif images are allowed.{/t}';
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
