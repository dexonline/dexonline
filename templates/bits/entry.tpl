{$editLink=$editLink|default:false}
{$link=$link|default:false}

{if $editLink}
  <a href="{$wwwRoot}editEntry.php?id={$entry->id}" title="editează">
    {$entry->description}
  </a>
{elseif $link}
  <a href="{$wwwRoot}intrare/{$entry->getShortDescription()}/{$entry->id}">
    {$entry->description}
  </a>
{else}
  <span class="entryName">{$entry->description}</span>
{/if}
