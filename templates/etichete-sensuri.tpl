<h3>Etichete pentru sensuri</h3>

<form method="get" action="etichete-sensuri">
  Adaugă etichete:
  <input type="text" name="value" value="" size="50" placeholder="una sau mai multe etichete, separate prin virgule"/>
  <input type="submit" name="submitButton" value="adaugă"/>
</form>
<br/>

{foreach from=$meaningTags item=mt}
  <div class="meaningTag">
    <div class="value">{$mt->value}</div>
    <div class="link">
      <a href="?deleteId={$mt->id}" title="șterge eticheta">x</a>
    </div>
  </div>
{/foreach}

<div style="clear: both"></div>
