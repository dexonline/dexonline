{extends "layout-admin.tpl"}

{block "title"}Editarea rolurilor autorilor în surse{/block}

{block "content"}

  <h3>Editarea rolurilor autorilor în surse</h3>

  <form method="post">
    <table class="table">
      <thead>
        <tr>
          <th>nume singular</th>
          <th>nume plural</th>
          <th>prioritate</th>
          <th></th>
        </tr>
      </thead>
      <tbody id="roleContainer">
        {include "bits/roleEditRow.tpl" id="stem"}
        {foreach $roles as $role}
          {include "bits/roleEditRow.tpl"}
        {/foreach}
      </tbody>
    </table>

    <div>
      <button id="addButton" class="btn btn-light" type="button">
        {include "bits/icon.tpl" i=add}
        adaugă un rol
      </button>

      <button class="btn btn-primary" type="submit" name="saveButton">
        {include "bits/icon.tpl" i=save}
        <u>s</u>alvează
      </button>

      <a class="btn btn-link" href="{Router::link('aggregate/dashboard')}">
        renunță
      </a>
    </div>

  </form>

{/block}
