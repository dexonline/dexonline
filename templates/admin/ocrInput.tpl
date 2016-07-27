{extends file="admin/layout.tpl"}

{block name=title}Adaugă definiții OCR din dicționar{/block}

{block name=headerTitle}Adaugă definiții OCR din dicționar{/block}

{block name=content}
  <form method="post" enctype="multipart/form-data">
    Sursa: {include file="bits/sourceDropDown.tpl" sources=$allModeratorSources skipAnySource=true}<br/>
    Moderator: {include file="bits/moderatorDropDown.tpl" name="editor" moderators=$allOCRModerators}<br/>
    <label for="file">Fișier: </label><input type="file" name="file" id="file"><br/>
    <input type="submit" name="submit" value="Încarcă">
  </form>
  <div class="{$msgClass}">{$message}</div>
{/block}

{block name=stats}
  <h4>Alocare definiții OCR</h4>
  <div class="adminSpace">
    <table id="editorStats">
      <tr>
        <th>Moderator</th>
        <th>Definiții finalizate</th>
        <th>Definiții alocate</th>
        <th>Caractere alocate</th>
      </tr>
      {foreach $statsEditors as $i}
        <tr>
          <td>{$i.0}</td>
          <td>{$i.1}</td>
          <td>{$i.2}</td>
          <td>{$i.4}</td>
        </tr>
      {/foreach}
    </table>
  </div>

  <h4>Dicționare prelucrate</h4>
  <div class="adminSpace">
    <table id="dictStats">
      <tr>
        <th>Preparator</th>
        <th>Dicționar</th>
        <th>Definiții preparate</th>
        <th>Definiții în lucru</th>
        <th>Nr. caractere preparate</th>
        <th>Nr. caractere în lucru</th>
      </tr>
      {foreach $statsPrep as $i}
        <tr>
          <td>{$i.0}</td>
          <td>{$i.1}</td>
          <td>{$i.2}</td>
          <td>{$i.3}</td>
          <td>{$i.4}</td>
          <td>{$i.5}</td>
        </tr>
      {/foreach}
    </table>
  </div>

{/block}
