<form action="definitionLookup.php" method="post">
  {foreach from=$args key=name item=value}
    <input type="hidden" name="{$name}" value="{$value}">
  {/foreach}
  <input type="submit" name="prevPageButton" value="<< înapoi">
  &nbsp; <b>pagina {$args.page}</b> &nbsp;
  <input type="submit" name="nextPageButton" value="înainte >>">
  <br><br>

  {include file="admin/definitionList.ihtml"}
  {if (count($searchResults))}
    <input type="submit" name="prevPageButton" value="<< înapoi">
    &nbsp; <b>pagina {$args.page}</b> &nbsp;
    <input type="submit" name="nextPageButton" value="înainte >>">
  {/if}
</form>
