{extends "layout-admin.tpl"}

{block "title"}Editarea rolurilor autorilor în surse{/block}

{block "content"}

  <h3>Editarea rolurilor autorilor în surse</h3>

  <form method="post">
    <table class="table">
      <thead>
        <tr>
          <th></th>
          <th>nume singular</th>
          <th>nume plural</th>
          <th class="">prioritate</th>
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
        <i class="glyphicon glyphicon-plus"></i>
        adaugă un rol
      </button>

      <button class="btn btn-success" type="submit" name="saveButton">
        <i class="glyphicon glyphicon-floppy-disk"></i>
        <u>s</u>alvează
      </button>

      <a class="btn btn-link" href="{Router::link('aggregate/dashboard')}">
        renunță
      </a>
    </div>

  </form>

{/block}
