{extends file="admin/layout.tpl"}

{block name=title}Imagini pentru cuvântul zilei{/block}

{block name=headerTitle}Imagini pentru cuvântul zilei{/block}

{block name=content}
  <a href="wotd.php">Lista cuvintelor zilei</a> |
  <a href="http://wiki.dexonline.ro/wiki/Imagini_pentru_cuv%C3%A2ntul_zilei">instrucțiuni</a>

  <div id="fileManager"></div>
  <br/>

  <script>
   $().ready(function() {
       $('#fileManager').elfinder({
           url: '../elfinder-connector/wotd_connector.php',
           lang: 'en'
       }).elfinder('instance');
   });
  </script>

  <form action="wotdCompressImages" method="post" enctype="multipart/form-data" target="_new">
    Comprimă imagini (o arhivă zip):
    <input type="file" name="file"/>
    <input type="submit" name="submitButton" value="Comprimă"/>      
  </form>
{/block}
