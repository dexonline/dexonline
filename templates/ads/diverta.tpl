{if $hasImage}
  {math equation="696-w" w=`$book->thumbWidth` assign=textWidth}
{else}
  {assign var=textWidth value=706}
{/if}
<a style="background: url('{$imgRoot}/diverta/bg.png') no-repeat; border: 0px; color: white; display: block; height: 90px; text-decoration: none; width: 728px;"
   href="{$clickurl}" target="_blank">
  {if $hasImage}
    <img style="border: 0px; float: left; height: {$book->thumbHeight}px; padding: 3px 0px 3px 10px; width: {$book->thumbWidth}px;"
         src="{$imgRoot}/diverta/thumb/{$book->sku}.jpg" alt="imagine copertă">
  {/if}
  <div style="float: left; height: 90px; overflow: hidden; padding: 0 10px; text-align: center; width: {$textWidth}px;">
    <div style="font-size: 20px; margin-top: 10px;">{$book->title|truncate:58}</div>
    <div style="font-size: 14px;">
      {if $book->author == '***'}
        &nbsp;
      {else}
        de {$book->author}
      {/if}
    </div>
    <div style="font-size: 14px; margin-top: 8px;">Volum disponibil acum la <b>dol.ro &mdash; Diverta Online</b></div>
  </div>
</a>
