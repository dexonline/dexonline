{extends "layout-admin.tpl"}

{block "title"}
  {if $artist->id}
    Editare autor
  {else}
    Adăugare autor
  {/if}
{/block}

{block "content"}
  <div class="card mb-3">
    <div class="card-header">
      {if $artist->id}
        Editare autor
      {else}
        Adăugare autor
      {/if}
    </div>

    <div class="card-body">
      <form method="post">
        <input type="hidden" name="id" value="{$artist->id}">

        <div class="mb-3">
          <label class="form-label">Nume</label>
          <input
            type="text"
            name="name"
            value="{$artist->name}"
            class="form-control">
        </div>
        <div class="mb-3">
          <label class="form-label">E-mail</label>
          <input
            type="text"
            name="email"
            value="{$artist->email}"
            class="form-control">
        </div>
        <div class="mb-3">
          <label class="form-label">Cod</label>
          <input
            type="text"
            name="label"
            value="{$artist->label}"
            class="form-control">
        </div>
        <div class="mb-3">
          <label class="form-label">Credite</label>
          <input
            type="text"
            name="credits"
            value="{$artist->credits|escape}"
            class="form-control">
        </div>

        <div class="form-check">
          <label class="form-check-label">
            <input
              type="checkbox"
              class="form-check-input"
              name="hidden"
              {if $artist->hidden}checked{/if}>
            ascuns
          </label>
        </div>

        <div class="form-check mb-3">
          <label class="form-check-label">
            <input
              type="checkbox"
              class="form-check-input"
              name="sponsor"
              {if $artist->sponsor}checked{/if}>
            sponsor
          </label>
          <div class="form-text">
            sponsorii nu sunt asignați automat în lunile viitoare
          </div>
        </div>

        <button class="btn btn-primary" type="submit" name="saveButton">
          {include "bits/icon.tpl" i=save}
          <u>s</u>alvează
        </button>
        <a class="btn btn-link" href="{Router::link('artist/list')}">
          {include "bits/icon.tpl" i=arrow_back}
          înapoi la lista de autori
        </a>

      </form>
    </div>
  </div>
{/block}
