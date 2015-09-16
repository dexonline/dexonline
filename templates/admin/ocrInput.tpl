{extends file="admin/layout.tpl"}

{block name=title}OCR Input{/block}

{block name=headerTitle}OCR Input{/block}

{block name=content}
  <form method="post" enctype="multipart/form-data">
    Sursa: {include file="sourceDropDown.tpl" sources=$allModeratorSources skipAnySource=true}<br/>
    Moderator: {include file="moderatorDropDown.tpl" name="editor" moderators=$allOCRModerators}<br/>
    <label for="file">Fișier:</label><input type="file" name="file" id="file"><br/>
    <input type="submit" name="submit" value="Încarcă">
  </form>
  <div class="{$msgClass}">{$message}</div>
{/block}
