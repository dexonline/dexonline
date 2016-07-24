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

    <div class="form-group">
      <label>Nume</label>
      <input type="text" name="name" value="{$artist->name}" size="50" class="form-control" />
    </div>
    <div class="form-group">
      <label>E-mail</label>
      <input type="text" name="email" value="{$artist->email}" size="50" class="form-control" />
    </div>
    <div class="form-group">
      <label>cod</label>
      <input type="text" name="label" value="{$artist->label}" size="30" class="form-control" />
    </div>
    <div class="form-group">
      <label>credite</label>
      <input type="text" name="credits" value="{$artist->credits|escape}" size="80" class="form-control" />
    </div>
    <input class="btn btn-primary" type="submit" name="submitButton" value="salvează" />
    <a class="btn btn-link" href="autori-imagini.php">înapoi la lista de autori</a>

  </form>
{/block}
