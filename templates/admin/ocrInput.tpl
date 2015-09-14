<form method="post" enctype="multipart/form-data">
Sursa: {include file="sourceDropDown.ihtml" sources=$allModeratorSources skipAnySource=true}<br/>
Moderator: {include file="moderatorDropDown.ihtml" name="editor" moderators=$allOCRModerators}<br/>
<label for="file">Fișier:</label><input type="file" name="file" id="file"><br/>
<input type="submit" name="submit" value="Încarcă">
</form>
<div class="{$msgClass}">{$message}</div>
