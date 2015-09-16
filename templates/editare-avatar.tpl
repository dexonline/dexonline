{extends file="layout.tpl"}

{block name=title}Editarea pozei de profil{/block}

{block name=content}
  <p class="paragraphTitle">Editarea imaginii de profil</p>

  Decupați o zonă pătrată din imaginea de mai jos. Zona selectată va fi redimensionată automat la 48x48 pixeli. Aceasta este dimensiunea standard a
  imaginii dumneavoastră de profil.

  <div id="rawAvatarContainer">
    <img id="jcropTarget" src="{$imgRoot}/user/{$rawFileName}?cb={1000000000|rand:9999999999}" alt="imaginea utilizatorului {$user->nick|escape}"/>
  </div>

  <p class="paragraphTitle">Rezultat</p>

  Rezultatul selecției dumneavoastră apare aici. Dacă totul arată bine, apăsați butonul Salvează.

  <form id="avatarForm" action="salvare-avatar" method="post">
    <div id="avatarPreviewContainer">
      <img id="jcropPreview" src="{$imgRoot}/user/{$rawFileName}?cb={1000000000|rand:9999999999}" alt="previzualizare" class="jcrop-preview" />
    </div>
    <input type="hidden" name="x0" value=""/>
    <input type="hidden" name="y0" value=""/>
    <input type="hidden" name="side" value=""/>
    <input type="submit" name="submit" value="Salvează"/>
    <a href="preferinte">renunță</a>
  </form>

  <script type="text/javascript">
   $(function() {
       var jcropOrigWidth, jcropOrigHeight;
       
       $('#jcropTarget').Jcrop({
           aspectRatio: 1,
           keySupport: false,
           onChange: updateJcropPreview,
           onSelect: updateJcropPreview,
       }, function() {
           var bounds = this.getBounds();
           jcropOrigWidth = bounds[0];
           jcropOrigHeight = bounds[1];
       });

       function updateJcropPreview(c) {
           if (parseInt(c.w) > 0) {
               var r = 48 / c.w;
               $('#avatarForm input[name=x0]').val(c.x);
               $('#avatarForm input[name=y0]').val(c.y);
               $('#avatarForm input[name=side]').val(c.w);

               $('#jcropPreview').css({
                   width: Math.round(r * jcropOrigWidth) + 'px',
                   height: Math.round(r * jcropOrigHeight) + 'px',
                   marginLeft: '-' + Math.round(r * c.x) + 'px',
                   marginTop: '-' + Math.round(r * c.y) + 'px'
               });
           }
       };
   });
  </script>
{/block}
