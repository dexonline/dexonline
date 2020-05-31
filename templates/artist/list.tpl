{extends "layout-admin.tpl"}

{block "title"}Autori ai imaginilor{/block}

{block "content"}

  <h3>Autori ai imaginilor</h3>

  <table class="table table-condensed table-striped">
    <tr>
      <th>nume</th>
      <th>activ</th>
      <th>e-mail</th>
      <th>cod</th>
      <th>acțiuni</th>
    </tr>
    {foreach $artists as $a}
      <tr>
        <td>{$a->name}</td>
        <td>{if not $a->hidden}<span class="glyphicon glyphicon-check"></span>{/if}</td>
        <td>{$a->email}</td>
        <td>{$a->label}</td>
        <td>
          <a href="{Router::link('artist/edit')}?id={$a->id}">editează</a>
          {if $a->canDelete}
            <a href="{Router::link('artist/edit')}?deleteId={$a->id}">șterge</a>
          {/if}
        </td>
      </tr>
    {/foreach}
  </table>

  <a class="btn btn-default" href="{Router::link('artist/edit')}">
    <i class="glyphicon glyphicon-plus"></i>
    adaugă un autor
  </a>

{/block}
