{extends file="admin/layout.tpl"}

{block name=title}Etichetare imagini{/block}

{block name=headerTitle}Etichetare imagini pentru definiții{/block}

{block name=content}
  <div> 
    <a href="visual.php">Pagina de imagini</a>
  </div>
  <br/>

  {include file="bits/prototypes.tpl"}
  <div id="jCropCanvas" class="imageHolder">
    <img id="jcrop" class="visualTagImg" src="{$cfg.static.url}/img/visual/{$visual->path}" alt="Imagine de etichetat"/>
  </div>

  <div id="tagOptions">
    <h3>Informații imagine</h3>

    <form class="inline" action="" method="post">
      <input id="visualId" type="hidden" name="id" value="{$visual->id}">
      Intrare asociată:
      <select id="entryId" name="entryId">
        <option value="{$visual->entryId}" selected></option>
      </select>
      <br>
      <input type="checkbox" id="revisedCheckbox" name="revised" value="1" {if $visual->revised}checked{/if}>
      <label for="revisedCheckbox">Etichetarea este completă</label>
      <br>
      <input type="submit" name="saveButton" value="actualizează">
    </form>

    <h3>Adaugă o etichetă nouă</h3>

    <form class="inline" action="" method="post">
      <input type="hidden" name="id" value="{$visual->id}">
      Intrare asociată:
      <select id="tagEntryId" name="tagEntryId">
      </select>
      <br>
      Text de afișat:
      <input id="tagLabel" type="text" name="tagLabel">
      <br>

      Coordonatele centrului etichetei:
      <input id="textXCoord" name="textXCoord" type="text" size="4">
      <input id="textYCoord" name="textYCoord" type="text" size="4">
      <button id="setTextCoords" type="button">setează coordonatele</button>
      <span class="tooltip2" title="Pentru a alege coordonatele unde să fie plasată eticheta, dați click pe imagine, apoi click pe „setează coordonatele”.
                                    Procedați la fel și pentru coordonatele vârfului săgeții.">&nbsp;</span>
      <br>

      Coordonatele vârfului săgeții:
      <input id="imgXCoord" name="imgXCoord" type="text" size="4">
      <input id="imgYCoord" name="imgYCoord" type="text" size="4">
      <button id="setImgCoords" type="button">setează coordonatele</button>
      <br>

      <input id="addTagButton" type="submit" name="addTagButton" value="salvează eticheta">
    </form>

    <h3>Previzualizează etichetele</h3>

    <form action="visualTag.php" method="post">
      <input type="hidden" value="{$visual->id}" name="id"/>
      <button id="previewTags" type="button">previzualizează etichetele</button>
    </form>
  </div>

  <div id="savedTags">
    <h3>Etichete existente</h3>
    <table id="tagsGrid"></table>
    <div id="tagsPaging"></div>
  </div>

  <p class="missingImageError">
    Hopa! Au apărut probleme la încărcarea imaginii.<br/>
    Dacă imaginea nu apare după un refresh, înseamnă că ea lipsește din
    din sistemul de fișiere. Pentru a te asigura că aceasta este 
    situația, mergi la pagina cu tabelele centralizatoare și verifică 
    dacă ea se găsește la linkul specificat. Dacă într-adevăr lipsește,
    te rog șterge-o din tabelul centralizator în care se află.
  </p>
{/block}
