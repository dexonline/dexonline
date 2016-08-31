{extends file="layout.tpl"}

{block name=title}Autori ai imaginilor{/block}

{block name=banner}{/block}
{block name=search}{/block}

{block name=content}

  <table class="table table-condensed table-bordered table-striped">
    <caption class="table-caption">
      Autori ai imaginilor
      <a class="btn btn-xs btn-success pull-right" href="editare-autor-imagini.php">
        <i class="glyphicon glyphicon-plus"></i>
        adaugă un autor
      </a>
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

{/block}
