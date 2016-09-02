{extends "layout-admin.tpl"}

{block name=title}Imagini pentru cuvântul zilei{/block}

{block name=content}

  <h3>Imagini pentru cuvântul zilei</h3>

  <div id="fileManager"></div>

  <h3>Pagini asociate</h3>

  <ul>

    <li>
      <a href="wotdTable.php">lista cuvintelor zilei</a>
    </li>

    <li>
      <a href="http://wiki.dexonline.ro/wiki/Imagini_pentru_cuv%C3%A2ntul_zilei"
         >instrucțiuni</a>
    </li>

  </ul>

  {** Unused for many years **}
  {**
  <form action="wotdCompressImages" method="post" enctype="multipart/form-data">
    Comprimă imagini (o arhivă zip):
    <input type="file" name="file"/>
    <input type="submit" name="submitButton" value="Comprimă"/>      
  </form>
  **}
{/block}
