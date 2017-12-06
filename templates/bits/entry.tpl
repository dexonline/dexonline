{$editLink=$editLink|default:false}
{$editLinkClass=$editLinkClass|default:''}
{$link=$link|default:false}
{$variantList=$variantList|default:false}
{$tagList=$tagList|default:false}

{if $editLink}
  <a href="{$wwwRoot}editEntry.php?id={$entry->id}" class="{$editLinkClass}" title="editează">
    {$entry->description}
  </a>
{elseif $link}
  <a href="{$wwwRoot}intrare/{$entry->getShortDescription()}/{$entry->id}">
    {$entry->description}
  </a>
{else}
  <span class="entryName">{$entry->description}</span>
{/if}

{if $variantList}
  <span class="variantList">
    {foreach $entry->getPrintableLexems() as $l}
      <span {if !$l->main}class="text-muted"{/if}>
        {$l->formNoAccent}
      </span>
    {/foreach}
  </span>
{/if}

{if $tagList}
  <span class="tagList">
    {foreach $entry->getTags() as $t}
      {include "bits/tag.tpl"}
    {/foreach}
  </span>
{/if}
