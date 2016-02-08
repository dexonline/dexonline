{extends file="layout.tpl"}

{block name=title}
  {if $artist->id}
    Editare autor
  {else}
    Adăugare autor
  {/if}
{/block}

{block name=content}
  <h3>
    {if $artist->id}
      Editare autor
    {else}
      Adăugare autor
    {/if}
  </h3>

  <form method="post">
    <input type="hidden" name="id" value="{$artist->id}"/>
    <table class="minimalistTable">
      <tr>
        <td>nume</td>
        <td><input type="text" name="name" value="{$artist->name}" size="50"/></td>
      </tr>
      <tr>
        <td>e-mail</td>
        <td><input type="text" name="email" value="{$artist->email}" size="50"/></td>
      </tr>
      <tr>
        <td>cod</td>
        <td><input type="text" name="label" value="{$artist->label}" size="30"/></td>
      </tr>
      <tr>
        <td>credite</td>
        <td><input type="text" name="credits" value="{$artist->credits|escape}" size="80"/></td>
      </tr>
      <tr>
        <td colspan="2">
          <input type="submit" name="submitButton" value="salvează"/>
          <a href="autori-imagini.php">înapoi la lista de autori</a>
        </td>
      </tr>
    </table>
  </form>
{/block}
