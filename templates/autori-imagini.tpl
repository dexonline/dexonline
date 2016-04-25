{extends file="layout.tpl"}

{block name=title}Autori ai imaginilor{/block}

{block name=content}
  <p class="paragraphTitle">Autori ai imaginilor</p>
  
  <table class="toolsTable minimalistTable">
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
  <a href="editare-autor-imagini.php">adaugă un autor</a>
{/block}
