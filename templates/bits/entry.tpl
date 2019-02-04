{$editLink=$editLink|default:false}
{$target=$target|default:'_self'}
{$editLinkClass=$editLinkClass|default:''}
{$link=$link|default:false}
{$variantList=$variantList|default:false}
{$tagList=$tagList|default:false}

{strip}
{if $editLink}
  <a
    href="{Config::URL_PREFIX}editEntry.php?id={$entry->id}"
    class="{$editLinkClass}"
    title="editează"
    target="{$target}">
    {$entry->description}
  </a>
{elseif $link}
  <a href="{Config::URL_PREFIX}intrare/{$entry->getShortDescription()}/{$entry->id}">
    {$entry->description}
  </a>
{else}
  <span class="entryName">{$entry->description}</span>
{/if}
{/strip}

{if $variantList}
  <span class="variantList">
    {foreach $entry->getPrintableLexemes() as $l}
      <span {if !$l->main}class="text-muted"{/if}>
        {$l->formNoAccent}
        {if $l->cnt > 1}({$l->cnt}){/if}
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
