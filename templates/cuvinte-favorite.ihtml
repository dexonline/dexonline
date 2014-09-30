<h2>Lista cuvintelor favorite pentru {$sUser->nick} ({$sUser->name})</h2>

<div class="favoriteDefs">
{if $bookmarks} 
  {foreach from=$bookmarks item=row key=i}
    <div class="favoriteDef">
      <b>{$i+1}.</b> <a href="{$wwwRoot}definitie/{$row->definitionId}">{$row->lexicon}</a> adăugat la {$row->createDate|date_format:"%e %b %Y"}
      <a class="bookmarkRemoveButton" href="{$wwwRoot}ajax/bookmarkRemove.php?definitionId={$row->definitionId}">Șterge</a><br/>
      <span>{$row->html}</span>
    </div>
  {/foreach}
  {literal}
  <script type="text/javascript">
  $(function () {
    $('.bookmarkRemoveButton').click(function () {
      removeBookmark($(this));
      return false;
    });
  });
  </script>
  {/literal}
{else}
  Nu aveți niciun cuvânt favorit.
{/if}
</div>
