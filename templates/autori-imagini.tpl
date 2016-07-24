{extends file="layout.tpl"}

{block name=title}Autori ai imaginilor{/block}

{block name=content}

  <table class="table table-condensed table-bordered table-striped">
    <caption class="table-caption text-center">
      Autori ai imaginilor
    </caption>
    <tr>
      <th>nume</th>
      <th>e-mail</th>
      <th>cod</th>
      <th>credite</th>
      <th>acțiuni</th>
    </tr>
    {foreach from=$artists item=a}
      <tr>
        <td>{$a->name}</td>
        <td>{$a->email}</td>
        <td>{$a->label}</td>
        <td>{$a->credits|escape}</td>
        <td>
          <a href="editare-autor-imagini.php?id={$a->id}">editează</a>
          {if $a->canDelete}
            <a href="editare-autor-imagini.php?deleteId={$a->id}">șterge</a>
          {/if}
        </td>
      </tr>
    {/foreach}
  </table>

  <a class="btn btn-primary" href="editare-autor-imagini.php">adaugă un autor</a>

{/block}
