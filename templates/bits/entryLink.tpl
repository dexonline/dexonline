{* $entry is an array of [0] = description, [1] = id, [2] = structStatus *}
{$editLink=$editLink|default:false}
{$editLinkClass=$editLinkClass|default:'primary'}

{if $editLink}
  <a href="{$wwwRoot}editEntry.php?id={$entry[1]}" class="{$editLinkClass}" title="editează">
    {$entry[0]}
  </a>
{/if}
