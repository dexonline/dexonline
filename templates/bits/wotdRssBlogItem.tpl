{if $imageUrl}
  <div class="separator" style="clear: both; text-align: center;"><a href="{$imageUrl}"  imageanchor="1" style="clear: left; float: left; margin-bottom: 1em; margin-right: 1em;"><img src="{$imageUrl}" alt="{$def->lexicon}"></a></div>
{/if}
{$def->getHtml()}
<br>
Sursa: <a class="ref" href="https://dexonline.ro/surse" title="{$source->name|escape}, {$source->year|escape}"
>{$source->shortName|escape}
{if $source->year}
({$source->year|regex_replace:"/ .*$/":""})
{/if}
</a>
